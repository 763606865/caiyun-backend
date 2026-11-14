<?php

use App\Admin\AdminPanelProvider;
use App\Libs\Oss\ServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AdminPanelProvider::class,
    ServiceProvider::class,
    AppServiceProvider::class,
    HorizonServiceProvider::class,
];
