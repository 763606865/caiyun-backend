<?php

namespace App\Admin\Pages;

use App\Models\AdminUser;
use App\Services\Platform\FulfillmentOperations;
use App\Services\Platform\FulfillmentReconciliation;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FulfillmentWorkbench extends Page
{
    use WithPagination;

    protected static ?string $title = '履约异常与对账工作台';

    protected static ?string $navigationLabel = '履约异常与对账';

    protected static string|\UnitEnum|null $navigationGroup = '中台管理';

    protected string $view = 'admin.pages.fulfillment-workbench';

    #[Url]
    public ?int $enterprise = null;

    #[Url(as: 'contract')]
    public string $contract = '';

    #[Url]
    public string $tab = 'exceptions';

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();

        return $admin instanceof AdminUser && $admin->is_active
            && Schema::hasTable('platform_fulfillment_operators')
            && app(FulfillmentOperations::class)->enterprises($admin)->exists();
    }

    public function updatedEnterprise(): void
    {
        $this->resetPagination();
        $this->contract = '';
    }

    public function updatedContract(): void
    {
        $this->resetPagination();
    }

    public function showTab(string $tab): void
    {
        abort_unless(in_array($tab, ['exceptions', 'checks', 'trace'], true), 404);
        $this->tab = $tab;
        $this->resetPagination();
    }

    public function trace(string $contract): void
    {
        abort_unless(Str::isUuid($contract), 422);
        $this->contract = $contract;
        $this->showTab('trace');
    }

    public function retry(string $uuid, string $direction): void
    {
        abort_unless($this->enterprise !== null, 403);
        try {
            $queued = app(FulfillmentOperations::class)->requestRetry($this->operator(), $this->enterprise, $uuid, $direction);
            Notification::make()->title($queued ? '重试已排队' : '该消息已有重试在等待')->success()->send();
        } catch (HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() !== 409) {
                throw $exception;
            }
            Notification::make()->title($exception->getMessage())->warning()->send();
        }
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $admin = $this->operator();
        $operations = app(FulfillmentOperations::class);
        $enterprises = $operations->enterprises($admin)->orderBy('name')->get(['id', 'name']);
        $data = ['enterprises' => $enterprises, 'amounts' => FulfillmentReconciliation::AMOUNTS];
        if ($this->enterprise === null) {
            return $data;
        }
        abort_unless($enterprises->contains('id', $this->enterprise), 403);
        $contract = strtolower(trim($this->contract));
        if ($contract !== '' && ! Str::isUuid($contract)) {
            return [...$data, 'invalidContract' => true];
        }
        $cutoff = now()->subMinutes(15);
        $messages = $operations->messages($this->enterprise)->when($contract !== '', fn ($q) => $q->where('m.correlation_uuid', $contract));
        $failures = $operations->failures($this->enterprise)->when($contract !== '', fn ($q) => $q->where('f.correlation_uuid', $contract));
        $checks = DB::table('platform_fulfillment_checks')->where('enterprise_id', $this->enterprise)
            ->when($contract !== '', fn ($q) => $q->where('correlation_uuid', $contract));
        $approvals = DB::table('platform_contract_approvals as c')
            ->join('platform_applications as a', 'a.id', '=', 'c.target_application_id')
            ->where('c.enterprise_id', $this->enterprise)
            ->when($contract !== '', fn ($q) => $q->where('c.contract_uuid', $contract))
            ->orderByDesc('c.id')->select(['c.uuid', 'c.contract_uuid', 'c.revision', 'c.status', 'c.updated_at', 'a.code as application', 'c.oa_contract_uuid', 'c.oa_instance_uuid']);
        $retries = DB::table('platform_fulfillment_retries as r')
            ->join('platform_applications as a', 'a.id', '=', 'r.application_id')
            ->leftJoin('admin_users as u', 'u.id', '=', 'r.admin_user_id')->where('r.enterprise_id', $this->enterprise)
            ->when($contract !== '', fn ($q) => $q->where('r.correlation_uuid', $contract))
            ->orderByDesc('r.id')->limit(20)->get(['r.message_uuid', 'r.direction', 'r.status', 'r.requested_at', 'r.finished_at', 'a.code as application', 'u.name as operator']);
        $data = [
            ...$data, 'canRetry' => $operations->enterprises($admin, retry: true)->where('id', $this->enterprise)->exists(),
            'retries' => $retries,
            'retryApps' => DB::table('platform_application_scopes as s')->join('platform_applications as a', 'a.id', '=', 's.application_id')
                ->where('s.enterprise_id', $this->enterprise)->where('s.scope', 'fulfillment.retry')->pluck('a.code')->all(),
        ];
        if ($this->tab === 'checks') {
            return [...$data, 'checks' => $checks->orderByDesc('id')->paginate(20)];
        }
        if ($this->tab === 'trace') {
            if ($contract === '') {
                return [...$data, 'needsContract' => true];
            }

            return [...$data, 'messages' => $messages->orderBy('m.id')->paginate(30, pageName: 'messagesPage'),
                'failures' => $failures->orderByDesc('f.id')->paginate(20, pageName: 'failuresPage'),
                'checks' => $checks->orderBy('id')->paginate(20, pageName: 'checksPage'),
                'approvals' => $approvals->paginate(20, pageName: 'approvalsPage'),
            ];
        }

        return [...$data,
            'approvals' => $approvals->where('c.status', 'rejected')
                ->whereNotExists(fn ($newer) => $newer->selectRaw('1')->from('platform_contract_approvals as newer')
                    ->whereColumn('newer.enterprise_id', 'c.enterprise_id')->whereColumn('newer.contract_uuid', 'c.contract_uuid')
                    ->whereColumn('newer.source_application_id', 'c.source_application_id')->whereColumn('newer.revision', '>', 'c.revision'))
                ->paginate(20, pageName: 'approvalsPage'),
            'overdue' => $messages->where('m.status', 'queued')->where('m.created_at', '<=', $cutoff)->orderBy('m.created_at')->paginate(20, pageName: 'messagesPage'),
            'failures' => $failures->orderByDesc('f.observed_at')->paginate(20, pageName: 'failuresPage'),
            'checks' => $checks->where(fn ($q) => $q->whereIn('status', ['mismatch', 'unverified', 'rejected'])
                ->orWhere(fn ($pending) => $pending->where('status', 'pending')->where('status_since', '<=', $cutoff)))
                ->orderBy('status_since')->paginate(20, pageName: 'checksPage'),
        ];
    }

    private function resetPagination(): void
    {
        foreach (['page', 'messagesPage', 'failuresPage', 'checksPage', 'approvalsPage'] as $name) {
            $this->resetPage($name);
        }
    }

    private function operator(): AdminUser
    {
        $admin = auth('admin')->user();
        abort_unless($admin instanceof AdminUser && $admin->is_active, 403);

        return $admin;
    }
}
