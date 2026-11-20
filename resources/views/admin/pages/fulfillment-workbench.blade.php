<x-filament-panels::page>
    @php
        $kinds = [
            'sales_order' => '销售订单', 'warehouse_delivery' => '发货指令', 'delivery_result' => '发货结果',
            'order_status' => '订单及结算状态', 'cancel_sales_order' => '取消销售订单', 'cancel_delivery' => '取消发货',
            'production_order' => '生产指令', 'production_result' => '生产结果', 'warehouse_receipt' => '入库指令',
            'receipt_result' => '入库结果', 'inventory' => '库存流水', 'finance' => '货款与退款', 'production' => '生产进度',
        ];
        $states = [
            'queued' => '排队中', 'done' => '已确认', 'approved' => '已审核', 'rejected' => '已驳回', 'paused' => '已暂停',
            'cancelled' => '已取消', 'draft' => '待审核 / 处理', 'submitted' => '已提交', 'processing' => '作业中',
            'closed' => '已结案', 'released' => '已下达', 'completed' => '重试完成', 'failed' => '重试失败',
            'posted' => '已过账', 'complete' => '已结束', 'awaiting' => '待业务处理', 'pending' => '等待另一侧同步',
            'matched' => '一致', 'mismatch' => '账差', 'unverified' => '证据不完整',
        ];
        $reasons = [
            'access' => '应用授权或签名校验失败', 'conflict' => '单据状态、映射或版本冲突',
            'validation' => '业务校验失败', 'unavailable' => '服务暂不可用', 'throttled' => '调用限流',
            'missing' => '所需单据或映射不存在', 'unknown' => '交换失败，需查看责任系统日志',
        ];
    @endphp

    <style>
        .fulfillment-controls { display:flex; flex-wrap:wrap; align-items:end; gap:1rem; }
        .fulfillment-controls > label { display:grid; gap:.375rem; flex:1 1 15rem; }
        .fulfillment-scroll { overflow-x:auto; }
        .fulfillment-table { width:100%; text-align:start; }
        .fulfillment-table th,.fulfillment-table td { padding:.625rem; border-bottom:1px solid color-mix(in srgb,currentColor 14%,transparent); text-align:start; vertical-align:top; }
        .fulfillment-table th { font-weight:600; }
        .fulfillment-table td > small { display:block; margin-top:.25rem; }
        .fulfillment-id { overflow-wrap:anywhere; font-family:ui-monospace,monospace; font-size:.78rem; }
        .fulfillment-hint { font-size:.875rem; opacity:.78; }
        .fulfillment-evidence { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,18rem),1fr)); gap:1rem; padding:.625rem 0; }
        .fulfillment-evidence li { padding:.125rem 0; overflow-wrap:anywhere; }
    </style>

    <div class="fulfillment-controls">
        <label>
            <span>企业</span>
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live.number="enterprise">
                    <option value="">选择企业</option>
                    @foreach ($enterprises as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </label>
        <label>
            <span>合同 UUID</span>
            <x-filament::input.wrapper>
                <x-filament::input type="search" wire:model.live.debounce.450ms="contract" maxlength="36" placeholder="输入合同 UUID 筛选" />
            </x-filament::input.wrapper>
        </label>
        <x-filament::button type="button" color="gray" wire:click="$refresh">刷新</x-filament::button>
    </div>

    @if ($enterprise)
        <x-filament::tabs label="履约工作台">
            <x-filament::tabs.item :active="$tab === 'exceptions'" wire:click="showTab('exceptions')">异常</x-filament::tabs.item>
            <x-filament::tabs.item :active="$tab === 'checks'" wire:click="showTab('checks')">对账</x-filament::tabs.item>
            <x-filament::tabs.item :active="$tab === 'trace'" wire:click="showTab('trace')">单据追踪</x-filament::tabs.item>
        </x-filament::tabs>
    @endif

    @if (! $enterprise)
        <p class="fulfillment-hint">选择企业后查看相关单据和对账结果。</p>
    @elseif ($invalidContract ?? false)
        <p role="alert">请输入完整的合同 UUID。</p>
    @elseif ($needsContract ?? false)
        <p class="fulfillment-hint">输入合同 UUID，或从异常与对账记录进入追踪。</p>
    @endif

    @if (isset($approvals))
        <x-filament::section :heading="$tab === 'trace' ? 'OA 审批' : '最新 OA 审批驳回'">
            <div class="fulfillment-scroll">
                <table class="fulfillment-table">
                    <thead><tr><th>版本 / 消息</th><th>责任应用</th><th>状态</th><th>OA 合同 / 流程 UUID</th><th>状态更新时间</th></tr></thead>
                    <tbody>
                        @forelse ($approvals as $approval)
                            <tr>
                                <td>
                                    修订 {{ $approval->revision }}<small class="fulfillment-id">{{ $approval->uuid }}</small>
                                    @if ($tab !== 'trace')
                                        <x-filament::button type="button" size="xs" color="gray" wire:click="trace('{{ $approval->contract_uuid }}')">追踪</x-filament::button>
                                    @endif
                                </td>
                                <td>{{ $approval->application }}</td>
                                <td>{{ in_array($approval->status, ['queued', 'pending'], true) ? '待 OA 审批' : ($states[$approval->status] ?? $approval->status) }}</td>
                                <td class="fulfillment-id">{{ $approval->oa_contract_uuid ?? '—' }}<br>{{ $approval->oa_instance_uuid ?? '—' }}</td>
                                <td>{{ $approval->updated_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">{{ $tab === 'trace' ? '当前企业无此合同审批记录。' : '没有符合条件的最新审批驳回。' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $approvals->links() }}
        </x-filament::section>
    @endif

    @if (isset($failures))
        <x-filament::section heading="应用交换失败">
            <div class="fulfillment-scroll">
                <table class="fulfillment-table">
                    <thead><tr><th>业务 / 原消息</th><th>责任应用</th><th>原因</th><th>观测 / 下次自动重试</th><th>操作</th></tr></thead>
                    <tbody>
                        @forelse ($failures as $failure)
                            <tr>
                                <td>{{ $kinds[$failure->kind] ?? $failure->kind }}<small class="fulfillment-id">{{ $failure->message_uuid }}</small></td>
                                <td>{{ $failure->application }} · {{ $failure->direction === 'send' ? '发送' : '接收' }}</td>
                                <td>{{ $reasons[$failure->reason] ?? $reasons['unknown'] }}<small>{{ $failure->attempts ? '已尝试 '.$failure->attempts.' 次' : '' }}</small></td>
                                <td>{{ $failure->observed_at }} UTC<small>{{ $failure->next_attempt_at ? $failure->next_attempt_at.' UTC' : '由应用调度' }}</small></td>
                                <td>
                                    <x-filament::button type="button" size="xs" color="gray" wire:click="trace('{{ $failure->correlation_uuid }}')">追踪</x-filament::button>
                                    @if ($canRetry && in_array($failure->application, $retryApps, true))
                                        <x-filament::button type="button" size="xs" wire:click="retry('{{ $failure->message_uuid }}', '{{ $failure->direction }}')" wire:confirm="对这条原消息请求一次重试？">重试</x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">当前没有应用报告的未解除失败。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $failures->links() }}
        </x-filament::section>
    @endif

    @if (isset($messages) || isset($overdue))
        @php
            $items = $messages ?? $overdue;
        @endphp
        <x-filament::section :heading="isset($messages) ? '跨系统消息' : '超过 15 分钟尚未确认'">
            <div class="fulfillment-scroll">
                <table class="fulfillment-table">
                    <thead><tr><th>业务 / 原消息</th><th>来源 → 接收方</th><th>状态</th><th>首次排队 / 最近确认</th><th>操作</th></tr></thead>
                    <tbody>
                        @forelse ($items as $message)
                            <tr>
                                <td>{{ $kinds[$message->kind] ?? $message->kind }}<small class="fulfillment-id">{{ $message->uuid }}</small></td>
                                <td>{{ $message->source }} → {{ $message->target }}</td>
                                <td>{{ $message->status === 'queued' ? '待接收确认' : ($states[$message->status] ?? $message->status) }}</td>
                                <td>{{ $message->created_at }}<small>{{ $message->status === 'done' ? $message->updated_at : '—' }}</small></td>
                                <td>
                                    @if (isset($overdue))
                                        <x-filament::button type="button" size="xs" color="gray" wire:click="trace('{{ $message->correlation_uuid }}')">追踪</x-filament::button>
                                    @endif
                                    @if ($canRetry && $message->status === 'queued' && in_array($message->target, $retryApps, true))
                                        <x-filament::button type="button" size="xs" wire:click="retry('{{ $message->uuid }}', 'receive')" wire:confirm="对这条原消息请求一次接收重试？">重试</x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">没有符合条件的消息。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links() }}
        </x-filament::section>
    @endif

    @if (isset($checks))
        <x-filament::section :heading="$tab === 'exceptions' ? '对账异常与长期待同步' : '本地过账与业务证据'">
            @if ($tab !== 'exceptions')
                <p class="fulfillment-hint">库存按请求、仓库和中台物料比较有符号基本数量。款项按同一 ERP 投影版本核对。缺少证据或版本滞后时，请查看单据追踪。</p>
            @endif
            <div class="fulfillment-scroll">
                <table class="fulfillment-table">
                    <thead><tr><th>业务 / 关联 UUID</th><th>状态</th><th>各系统证据</th><th>操作</th></tr></thead>
                    <tbody>
                        @forelse ($checks as $check)
                            @php
                                $left = json_decode($check->left_snapshot ?? 'null', true);
                                $right = json_decode($check->right_snapshot ?? 'null', true);
                                $labels = match ($check->kind) { 'inventory' => ['WMS', 'ERP'], 'finance' => ['ERP', 'CRM'], default => ['MES'] };
                            @endphp
                            <tr>
                                <td>
                                    {{ $kinds[$check->kind] }} · {{ $kinds[$check->subtype] }}
                                    <small class="fulfillment-id">{{ $check->subject_uuid }}</small>
                                </td>
                                <td>
                                    <x-filament::badge :color="match ($check->status) { 'mismatch', 'rejected' => 'danger', 'matched', 'complete' => 'success', 'pending', 'unverified' => 'warning', default => 'gray' }">{{ $states[$check->status] }}</x-filament::badge>
                                    <small>{{ $check->status_since }}</small>
                                </td>
                                <td>
                                    <details @if ($check->status === 'mismatch') open @endif>
                                        <summary>展开单据与账据</summary>
                                        <div class="fulfillment-evidence">
                                            @foreach ($labels as $i => $label)
                                                @php
                                                    $snapshot = $i === 0 ? $left : $right;
                                                    $observedAt = $i === 0 ? $check->left_observed_at : $check->right_observed_at;
                                                @endphp
                                                <div>
                                                    <strong>{{ $label }}</strong>
                                                    @if ($snapshot === null)
                                                        <p class="fulfillment-hint">暂无上报证据</p>
                                                    @else
                                                        <ul>
                                                            <li>{{ $snapshot['document_number'] ?? $snapshot['document_uuid'] ?? '暂无本地单据' }}</li>
                                                            @if (isset($snapshot['document_number'], $snapshot['document_uuid']))
                                                                <li class="fulfillment-id">{{ $snapshot['document_uuid'] }}</li>
                                                            @endif
                                                            <li class="fulfillment-hint">采集于 {{ $observedAt }} UTC</li>
                                                            @if (isset($snapshot['status']))
                                                                <li>{{ $snapshot['status'] === 'pending' ? '待审核或接收' : ($states[$snapshot['status']] ?? $snapshot['status']) }}</li>
                                                            @endif
                                                            @if (isset($snapshot['operator_id']))
                                                                <li>本地操作成员 #{{ $snapshot['operator_id'] }}</li>
                                                            @endif
                                                            @if ($check->kind === 'finance')
                                                                <li>版本 {{ $snapshot['version'] }} · {{ $snapshot['currency'] }}</li>
                                                                @foreach ($amounts as $field => $caption)
                                                                    <li>{{ $caption }} {{ $snapshot['amounts'][$field] }}</li>
                                                                @endforeach
                                                            @elseif ($check->kind === 'inventory')
                                                                <li>仓库 <span class="fulfillment-id">{{ $snapshot['warehouse_uuid'] ?? '缺少绑定' }}</span></li>
                                                                @foreach ($snapshot['quantities'] as $product => $quantity)
                                                                    <li><span class="fulfillment-id">{{ $product }}</span> {{ $quantity }}</li>
                                                                @endforeach
                                                            @endif
                                                            @if (($snapshot['complete'] ?? true) === false)
                                                                <li>证据不完整</li>
                                                            @endif
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                </td>
                                <td>
                                    @if ($tab !== 'trace')
                                        <x-filament::button type="button" size="xs" color="gray" wire:click="trace('{{ $check->correlation_uuid }}')">追踪</x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">没有符合条件的对账记录。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $checks->links() }}
        </x-filament::section>
    @endif

    @if (isset($retries) && $retries->isNotEmpty())
        <x-filament::section heading="最近 20 次人工重试">
            <div class="fulfillment-scroll">
                <table class="fulfillment-table">
                    <thead><tr><th>原消息</th><th>操作人</th><th>执行应用</th><th>状态</th><th>请求 / 完成时间</th></tr></thead>
                    <tbody>
                        @foreach ($retries as $retry)
                            <tr>
                                <td class="fulfillment-id">{{ $retry->message_uuid }}</td>
                                <td>{{ $retry->operator ?? '账号已移除' }}</td>
                                <td>{{ $retry->application }} · {{ $retry->direction === 'send' ? '发送' : '接收' }}</td>
                                <td>{{ $states[$retry->status] }}</td>
                                <td>{{ $retry->requested_at }}<small>{{ $retry->finished_at ?? '—' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
