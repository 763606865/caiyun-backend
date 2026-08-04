import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
const EditClientVersion = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditClientVersion.url(args, options),
    method: 'get',
})

EditClientVersion.definition = {
    methods: ["get","head"],
    url: '/admin/client-versions/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
EditClientVersion.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { record: args }
    }

    if (Array.isArray(args)) {
        args = {
            record: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        record: args.record,
    }

    return EditClientVersion.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
EditClientVersion.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditClientVersion.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
EditClientVersion.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditClientVersion.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
const EditClientVersionForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditClientVersion.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
EditClientVersionForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditClientVersion.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\ClientVersions\Pages\EditClientVersion::__invoke
* @see app/Admin/Resources/ClientVersions/Pages/EditClientVersion.php:7
* @route '/admin/client-versions/{record}/edit'
*/
EditClientVersionForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditClientVersion.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditClientVersion.form = EditClientVersionForm

export default EditClientVersion