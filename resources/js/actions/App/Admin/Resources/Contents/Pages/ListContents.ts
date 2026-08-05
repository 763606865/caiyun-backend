import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
const ListContents = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListContents.url(options),
    method: 'get',
})

ListContents.definition = {
    methods: ["get","head"],
    url: '/admin/contents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
ListContents.url = (options?: RouteQueryOptions) => {
    return ListContents.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
ListContents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListContents.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
ListContents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListContents.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
const ListContentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContents.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
ListContentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContents.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\ListContents::__invoke
* @see app/Admin/Resources/Contents/Pages/ListContents.php:7
* @route '/admin/contents'
*/
ListContentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContents.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListContents.form = ListContentsForm

export default ListContents