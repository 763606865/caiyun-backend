<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformApplicationGrant extends Model
{
    protected $table = 'platform_application_grants';

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

    /** @return BelongsTo<PlatformEmployee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(PlatformEmployee::class);
    }
}
