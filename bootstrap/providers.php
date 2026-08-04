<?php

use App\Admin\AdminPanelProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    App\Libs\Oss\ServiceProvider::class,
];
