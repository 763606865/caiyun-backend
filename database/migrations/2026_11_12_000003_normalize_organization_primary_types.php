<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 固定本次迁移的映射，避免后续枚举变更影响历史数据迁移。
        $types = [
            'enterprise' => '企业',
            'school' => '学校/教育机构',
            'government' => '党政机关',
            'public_institution' => '事业单位',
            'association' => '协会/社会团体',
            'law_firm' => '律师事务所',
            'hr_agency' => '人力资源服务机构',
            'medical' => '医疗卫生机构',
            'foundation' => '基金会',
            'community' => '基层自治组织',
            'other' => '其他组织',
        ];
        $labels = array_flip($types);

        DB::table('platform_organizations')
            ->select(['id', 'primary_type'])
            ->whereNotNull('primary_type')
            ->orderBy('id')
            ->chunkById(500, function ($records) use ($types, $labels): void {
                foreach ($records as $record) {
                    if (isset($types[$record->primary_type])) {
                        continue;
                    }

                    DB::table('platform_organizations')->where('id', $record->id)->update([
                        'primary_type' => $labels[$record->primary_type] ?? 'other',
                    ]);
                }
            });
    }

    public function down(): void
    {
        // 原字段支持自由字符串，回滚时保留规范化值，不恢复无法还原的自定义类型。
    }
};
