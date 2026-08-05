import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
const ListOperationLogs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListOperationLogs.url(options),
    method: 'get',
})

ListOperationLogs.definition = {
    methods: ["get","head"],
    url: '/admin/operation-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
ListOperationLogs.url = (options?: RouteQueryOptions) => {
    return ListOperationLogs.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
ListOperationLogs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListOperationLogs.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
ListOperationLogs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListOperationLogs.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
const ListOperationLogsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListOperationLogs.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
ListOperationLogsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListOperationLogs.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\OperationLogs\Pages\ListOperationLogs::__invoke
* @see app/Admin/Resources/OperationLogs/Pages/ListOperationLogs.php:7
* @route '/admin/operation-logs'
*/
ListOperationLogsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListOperationLogs.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListOperationLogs.form = ListOperationLogsForm

export default ListOperationLogs