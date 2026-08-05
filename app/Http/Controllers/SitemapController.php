<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $contents = Content::query()->published()->select(['slug', 'updated_at'])->latest('updated_at')->get();

        return response()->view('cms.sitemap', compact('contents'))->header('Content-Type', 'application/xml');
    }
}
