import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/operation-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
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

const operationLogs = {
    index: Object.assign(index, index),
}

export default operationLogs