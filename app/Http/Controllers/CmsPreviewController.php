<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Contracts\View\View;

class CmsPreviewController extends Controller
{
    public function __invoke(Content $content): View
    {
        return view('cms.preview', ['content' => $content->load('categories', 'tags')]);
    }
}
