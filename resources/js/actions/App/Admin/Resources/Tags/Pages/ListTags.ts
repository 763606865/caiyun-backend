import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
const ListTags = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListTags.url(options),
    method: 'get',
})

ListTags.definition = {
    methods: ["get","head"],
    url: '/admin/tags',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
ListTags.url = (options?: RouteQueryOptions) => {
    return ListTags.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
ListTags.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListTags.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
ListTags.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListTags.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
const ListTagsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListTags.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
ListTagsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListTags.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\ListTags::__invoke
* @see app/Admin/Resources/Tags/Pages/ListTags.php:7
* @route '/admin/tags'
*/
ListTagsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListTags.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListTags.form = ListTagsForm

export default ListTags