<?php

use App\Http\Controllers\CmsPreviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('/cms/preview/{content}', CmsPreviewController::class)->middleware('signed')->name('cms.preview');
Route::get('/sitemap.xml', SitemapController::class)->name('cms.sitemap');
