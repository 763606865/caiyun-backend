<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformIntegrationAudit extends Model
{
    protected $table = 'platform_integration_audits';

    protected $guarded = ['*'];

    public $timestamps = false;

    /** @return BelongsTo<PlatformApplication, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(PlatformApplication::class);
    }

    /** @return BelongsTo<PlatformEnterprise, $this> */
    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(PlatformEnterprise::class);
    }
}
