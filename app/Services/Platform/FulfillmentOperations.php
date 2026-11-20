<?php

namespace App\Services\Platform;

use App\Models\AdminUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FulfillmentOperations
{
    public function enterprises(AdminUser $admin, bool $retry = false): Builder
    {
        return DB::table('platform_enterprises')->where('is_enabled', true)
            ->unless($admin->hasRole('super-admin'), fn (Builder $query) => $query->whereExists(
                fn (Builder $grant) => $grant->selectRaw('1')->from('platform_fulfillment_operators')
                    ->whereColumn('enterprise_id', 'platform_enterprises.id')->where('admin_user_id', $admin->id)
                    ->when($retry, fn (Builder $grant) => $grant->where('can_retry', true))
            ));
    }

    public function messages(int $enterprise): Builder
    {
        return DB::table('platform_business_messages as m')
            ->join('platform_applications as src', 'src.id', '=', 'm.source_application_id')
            ->join('platform_applications as dst', 'dst.id', '=', 'm.target_application_id')
            ->where('m.enterprise_id', $enterprise)
            ->select('m.uuid', 'm.kind', 'm.status', 'm.correlation_uuid', 'm.created_at', 'm.updated_at', 'src.code as source', 'dst.code as target');
    }

    public function failures(int $enterprise): Builder
    {
        return DB::table('platform_fulfillment_failures as f')
            ->join('platform_applications as app', 'app.id', '=', 'f.application_id')
            ->where('f.enterprise_id', $enterprise)
            ->whereNotExists(function (Builder $known): void {
                // A delivered envelope supersedes the send error; an ACK supersedes the receive error.
                $known->selectRaw('1')->from('platform_business_messages as m')
                    ->whereColumn('m.uuid', 'f.message_uuid')->whereColumn('m.enterprise_id', 'f.enterprise_id')
                    ->where(fn (Builder $done) => $done->where('f.direction', 'send')->orWhere('m.status', 'done'));
            })
            ->select('f.message_uuid', 'f.correlation_uuid', 'f.direction', 'f.reason', 'f.kind', 'f.attempts', 'f.observed_at', 'f.next_attempt_at', 'app.code as application');
    }

    public function requestRetry(AdminUser $admin, int $enterprise, string $uuid, string $direction): bool
    {
        abort_unless($admin->is_active && Str::isUuid($uuid) && in_array($direction, ['send', 'receive'], true), 403);

        return DB::transaction(function () use ($admin, $enterprise, $uuid, $direction): bool {
            // Serialize requests within a tenant to prevent multiple operators queuing the same retry.
            abort_unless($this->enterprises($admin, retry: true)->where('id', $enterprise)->lockForUpdate()->first(['id']) !== null, 403);
            $message = DB::table('platform_business_messages')->where('enterprise_id', $enterprise)->where('uuid', $uuid)->first();
            if ($direction === 'send') {
                abort_if($message !== null, 409, '原消息已到达中台');
                $failure = DB::table('platform_fulfillment_failures')->where('enterprise_id', $enterprise)
                    ->where('message_uuid', $uuid)->where('direction', 'send')->firstOrFail();
                $application = $failure->application_id;
                $kind = $failure->kind;
                $root = $failure->correlation_uuid;
            } else {
                abort_unless($message !== null && $message->status === 'queued', 409, '只可重试仍待确认的原消息');
                $application = $message->target_application_id;
                $kind = $message->kind;
                $root = $message->correlation_uuid;
            }
            $identity = ['enterprise_id' => $enterprise, 'application_id' => $application, 'message_uuid' => $uuid, 'direction' => $direction];
            $available = DB::table('platform_application_scopes')->where('enterprise_id', $enterprise)
                ->where('application_id', $application)->where('scope', 'fulfillment.retry')->exists();
            abort_unless($available, 409, '该企业的目标应用尚未开通工作台重试');
            if (DB::table('platform_fulfillment_retries')->where($identity)->where('status', 'queued')->exists()) {
                return false;
            }
            DB::table('platform_fulfillment_retries')->insert([
                ...$identity, 'uuid' => (string) Str::uuid7(), 'correlation_uuid' => $root, 'kind' => $kind,
                'admin_user_id' => $admin->id, 'requested_at' => now(),
            ]);

            return true;
        }, 3);
    }
}
