<?php

namespace Tests\Feature\Api;

use App\Enums\RequirementStatus;
use App\Enums\RequirementType;
use App\Models\CustomerRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRequirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_requirement(): void
    {
        $this->postJson('/api/customer-requirements', [
            'contact_name' => '张三',
            'contact_method' => 'zhangsan@example.com',
            'organization' => '彩云科技',
            'requirement_type' => RequirementType::WebAdmin->value,
            'requirements' => '需要开发一套订单和库存管理后台。',
        ])->assertCreated()
            ->assertJsonPath('code', 201)
            ->assertJsonPath('data.message', '需求已提交，我们会尽快与您联系。');

        $this->assertDatabaseHas('customer_requirements', [
            'contact_name' => '张三',
            'organization' => '彩云科技',
            'requirement_type' => RequirementType::WebAdmin->value,
            'status' => RequirementStatus::Pending->value,
        ]);
    }

    public function test_submission_rejects_invalid_type_and_short_requirement(): void
    {
        $this->postJson('/api/customer-requirements', [
            'contact_name' => '张三',
            'contact_method' => '13800138000',
            'organization' => '彩云科技',
            'requirement_type' => 'unknown',
            'requirements' => '太短',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['requirement_type', 'requirements']);

        $this->assertDatabaseCount('customer_requirements', 0);
    }

    public function test_follow_up_changes_automatically_record_the_time(): void
    {
        $requirement = CustomerRequirement::query()->create([
            'contact_name' => '张三',
            'contact_method' => '13800138000',
            'organization' => '彩云科技',
            'requirement_type' => RequirementType::WebsiteCms,
            'requirements' => '需要建设一个企业官网和内容管理系统。',
            'status' => RequirementStatus::Pending,
        ]);

        self::assertNull($requirement->followed_up_at);
        $requirement->update([
            'status' => RequirementStatus::FollowedUp,
            'follow_up_notes' => '已电话联系，等待需求文档。',
        ]);

        self::assertNotNull($requirement->fresh()->followed_up_at);
    }
}
