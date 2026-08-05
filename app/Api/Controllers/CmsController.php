<?php

namespace App\Api\Controllers;

use App\Models\Category;
use App\Models\Content;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()->enabled()->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->enabled()->with('children')])
            ->orderBy('sort')->get();

        return ApiResponse::success($categories);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $contents = Content::query()->published()->with(['categories:id,name,slug', 'tags:id,name,slug'])
            ->when($validated['category'] ?? null, fn ($query, $slug) => $query->whereHas('categories', fn ($q) => $q->where('slug', $slug)->where('is_enabled', true)))
            ->when($validated['tag'] ?? null, fn ($query, $slug) => $query->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->when($validated['q'] ?? null, fn ($query, $keyword) => $query->where(fn ($q) => $q->where('title', 'like', "%{$keyword}%")->orWhere('summary', 'like', "%{$keyword}%")))
            ->when(array_key_exists('featured', $validated), fn ($query) => $query->where('is_featured', $validated['featured']))
            ->orderByDesc('is_pinned')->orderByDesc('published_at')->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 15);

        return ApiResponse::success($contents);
    }

    public function show(string $slug): JsonResponse
    {
        $content = Content::query()->published()->where('slug', $slug)
            ->with(['categories:id,name,slug', 'tags:id,name,slug'])->firstOrFail();
        Content::query()->whereKey($content->id)->increment('view_count');

        return ApiResponse::success($content);
    }
}
