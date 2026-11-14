<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformDirectorySource extends Model
{
    protected $table = 'platform_directory_sources';

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
