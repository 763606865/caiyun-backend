{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($contents as $content)<url><loc>{{ url('/api/cms/contents/'.$content->slug) }}</loc><lastmod>{{ $content->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
