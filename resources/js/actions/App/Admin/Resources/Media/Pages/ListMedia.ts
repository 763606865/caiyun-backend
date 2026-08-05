import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
const ListMedia = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListMedia.url(options),
    method: 'get',
})

ListMedia.definition = {
    methods: ["get","head"],
    url: '/admin/media',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
ListMedia.url = (options?: RouteQueryOptions) => {
    return ListMedia.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
ListMedia.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
ListMedia.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListMedia.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
const ListMediaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
ListMediaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\ListMedia::__invoke
* @see app/Admin/Resources/Media/Pages/ListMedia.php:7
* @route '/admin/media'
*/
ListMediaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListMedia.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListMedia.form = ListMediaForm

export default ListMedia