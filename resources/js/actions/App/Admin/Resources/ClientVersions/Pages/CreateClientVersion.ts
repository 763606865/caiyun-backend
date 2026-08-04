import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
const CreateClientVersion = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateClientVersion.url(options),
    method: 'get',
})

CreateClientVersion.definition = {
    methods: ["get","head"],
    url: '/admin/client-versions/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
CreateClientVersion.url = (options?: RouteQueryOptions) => {
    return CreateClientVersion.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
CreateClientVersion.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateClientVersion.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
CreateClientVersion.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateClientVersion.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
const CreateClientVersionForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateClientVersion.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
CreateClientVersionForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateClientVersion.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\CreateClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/CreateClientVersion.php:7
* @route '/admin/client-versions/create'
*/
CreateClientVersionForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateClientVersion.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateClientVersion.form = CreateClientVersionForm

export default CreateClientVersion