import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
const ListClientVersions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListClientVersions.url(options),
    method: 'get',
})

ListClientVersions.definition = {
    methods: ["get","head"],
    url: '/admin/client-versions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
ListClientVersions.url = (options?: RouteQueryOptions) => {
    return ListClientVersions.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
ListClientVersions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListClientVersions.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
ListClientVersions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListClientVersions.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
const ListClientVersionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListClientVersions.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
ListClientVersionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListClientVersions.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\ListClientVersions::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/ListClientVersions.php:7
* @route '/admin/client-versions'
*/
ListClientVersionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListClientVersions.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListClientVersions.form = ListClientVersionsForm

export default ListClientVersions