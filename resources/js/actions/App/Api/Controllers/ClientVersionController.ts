import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
export const check = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: check.url(options),
    method: 'get',
})

check.definition = {
    methods: ["get","head"],
    url: '/api/client/version/check',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
check.url = (options?: RouteQueryOptions) => {
    return check.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
check.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: check.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
check.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: check.url(options),
    method: 'head',
})

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
const checkForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: check.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
checkForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: check.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\ClientVersionController::check
* @see app/Api/Controllers/ClientVersionController.php:16
* @route '/api/client/version/check'
*/
checkForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: check.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

check.form = checkForm

const ClientVersionController = { check }

export default ClientVersionController