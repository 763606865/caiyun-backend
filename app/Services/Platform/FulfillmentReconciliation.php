<?php

namespace App\Services\Platform;

use Brick\Math\BigDecimal;

class FulfillmentReconciliation
{
    public const AMOUNTS = [
        'receivable_amount' => '原始应收',
        'received_amount' => '已收款',
        'return_receivable_offset' => '退货冲减',
        'net_unreceived_amount' => '净未收',
        'refund_due' => '应退款',
        'refund_paid' => '已退款',
    ];

    /**
     * @param  array<string, mixed>|null  $left
     * @param  array<string, mixed>|null  $right
     */
    public function status(string $kind, ?array $left, ?array $right): string
    {
        if ($kind === 'production') {
            return ($left['status'] ?? null) === 'closed' ? 'complete' : 'awaiting';
        }
        if ($kind === 'finance') {
            if ($left === null || $right === null || $right['version'] < $left['version']) {
                return 'pending';
            }
            if ($left['version'] !== $right['version'] || ! $left['complete'] || ! $right['complete']) {
                return 'unverified';
            }

            return $left['currency'] === $right['currency'] && $this->equalAmounts($left['amounts'], $right['amounts']) ? 'matched' : 'mismatch';
        }

        $leftStatus = $left['status'] ?? null;
        $rightStatus = $right['status'] ?? null;
        $leftPosted = $leftStatus === 'posted';
        $rightPosted = $rightStatus === 'posted';
        if ($leftPosted !== $rightPosted) {
            $other = $leftPosted ? $right : $left;

            return in_array($other['status'] ?? null, ['cancelled', 'rejected'], true) ? 'mismatch' : 'pending';
        }
        if (! $leftPosted) {
            if ($leftStatus === 'rejected' || $rightStatus === 'rejected') {
                return 'rejected';
            }

            return $leftStatus === 'cancelled' && $rightStatus === 'cancelled' ? 'complete' : 'awaiting';
        }
        if (! $left['complete'] || ! $right['complete']) {
            return 'unverified';
        }

        return $left['warehouse_uuid'] === $right['warehouse_uuid'] && $this->equalAmounts($left['quantities'], $right['quantities']) ? 'matched' : 'mismatch';
    }

    /**
     * @param  array<string, string>  $left
     * @param  array<string, string>  $right
     */
    private function equalAmounts(array $left, array $right): bool
    {
        if (count($left) !== count($right)) {
            return false;
        }
        foreach ($left as $key => $value) {
            if (! array_key_exists($key, $right) || ! BigDecimal::of($value)->isEqualTo($right[$key])) {
                return false;
            }
        }

        return true;
    }
}
