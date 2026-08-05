import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
export const preview = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

preview.definition = {
    methods: ["get","head"],
    url: '/cms/preview/{content}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
preview.url = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { content: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { content: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            content: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        content: typeof args.content === 'object'
        ? args.content.id
        : args.content,
    }

    return preview.definition.url
            .replace('{content}', parsedArgs.content.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
preview.get = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
preview.head = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: preview.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
const previewForm = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
previewForm.get = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CmsPreviewController::__invoke
* @see app/Http/Controllers/CmsPreviewController.php:10
* @route '/cms/preview/{content}'
*/
previewForm.head = (args: { content: number | { id: number } } | [content: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: preview.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

preview.form = previewForm

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
export const sitemap = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sitemap.url(options),
    method: 'get',
})

sitemap.definition = {
    methods: ["get","head"],
    url: '/sitemap.xml',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
sitemap.url = (options?: RouteQueryOptions) => {
    return sitemap.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
sitemap.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sitemap.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
sitemap.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sitemap.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
const sitemapForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sitemap.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
sitemapForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sitemap.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SitemapController::__invoke
* @see app/Http/Controllers/SitemapController.php:10
* @route '/sitemap.xml'
*/
sitemapForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sitemap.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

sitemap.form = sitemapForm

const cms = {
    preview: Object.assign(preview, preview),
    sitemap: Object.assign(sitemap, sitemap),
}

export default cms