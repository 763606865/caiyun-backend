<?php

use App\Enums\ContentStatus;
use App\Models\Content;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function (): void {
    Content::query()->where('status', ContentStatus::Pending)->whereNotNull('published_at')->where('published_at', '<=', now())
        ->each(fn (Content $content) => $content->publish());
    Content::query()->where('status', ContentStatus::Published)->whereNotNull('offline_at')->where('offline_at', '<=', now())
        ->update(['status' => ContentStatus::Offline]);
})->everyMinute()->name('cms:publish-scheduled')->withoutOverlapping();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
