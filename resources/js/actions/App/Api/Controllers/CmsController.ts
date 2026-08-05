import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
export const categories = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: categories.url(options),
    method: 'get',
})

categories.definition = {
    methods: ["get","head"],
    url: '/api/cms/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
categories.url = (options?: RouteQueryOptions) => {
    return categories.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
categories.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: categories.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
categories.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: categories.url(options),
    method: 'head',
})

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
const categoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: categories.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
categoriesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: categories.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::categories
* @see app/Api/Controllers/CmsController.php:13
* @route '/api/cms/categories'
*/
categoriesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: categories.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

categories.form = categoriesForm

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/cms/contents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::index
* @see app/Api/Controllers/CmsController.php:22
* @route '/api/cms/contents'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
export const show = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/cms/contents/{slug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
show.url = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { slug: args }
    }

    if (Array.isArray(args)) {
        args = {
            slug: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        slug: args.slug,
    }

    return show.definition.url
            .replace('{slug}', parsedArgs.slug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
show.get = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
show.head = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
const showForm = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
showForm.get = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\CmsController::show
* @see app/Api/Controllers/CmsController.php:43
* @route '/api/cms/contents/{slug}'
*/
showForm.head = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const CmsController = { categories, index, show }

export default CmsController