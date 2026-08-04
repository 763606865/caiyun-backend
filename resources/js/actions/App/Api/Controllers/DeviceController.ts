import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\DeviceController::sync
* @see app/Api/Controllers/DeviceController.php:18
* @route '/api/devices/sync'
*/
export const sync = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

sync.definition = {
    methods: ["post"],
    url: '/api/devices/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\DeviceController::sync
* @see app/Api/Controllers/DeviceController.php:18
* @route '/api/devices/sync'
*/
sync.url = (options?: RouteQueryOptions) => {
    return sync.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\DeviceController::sync
* @see app/Api/Controllers/DeviceController.php:18
* @route '/api/devices/sync'
*/
sync.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\DeviceController::sync
* @see app/Api/Controllers/DeviceController.php:18
* @route '/api/devices/sync'
*/
const syncForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sync.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\DeviceController::sync
* @see app/Api/Controllers/DeviceController.php:18
* @route '/api/devices/sync'
*/
syncForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sync.url(options),
    method: 'post',
})

sync.form = syncForm

const DeviceController = { sync }

export default DeviceController