import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\LicenseLeaseController::__invoke
* @see app/Api/Controllers/LicenseLeaseController.php:12
* @route '/api/v1/leases'
*/
const LicenseLeaseController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LicenseLeaseController.url(options),
    method: 'post',
})

LicenseLeaseController.definition = {
    methods: ["post"],
    url: '/api/v1/leases',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\LicenseLeaseController::__invoke
* @see app/Api/Controllers/LicenseLeaseController.php:12
* @route '/api/v1/leases'
*/
LicenseLeaseController.url = (options?: RouteQueryOptions) => {
    return LicenseLeaseController.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\LicenseLeaseController::__invoke
* @see app/Api/Controllers/LicenseLeaseController.php:12
* @route '/api/v1/leases'
*/
LicenseLeaseController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: LicenseLeaseController.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\LicenseLeaseController::__invoke
* @see app/Api/Controllers/LicenseLeaseController.php:12
* @route '/api/v1/leases'
*/
const LicenseLeaseControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LicenseLeaseController.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\LicenseLeaseController::__invoke
* @see app/Api/Controllers/LicenseLeaseController.php:12
* @route '/api/v1/leases'
*/
LicenseLeaseControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: LicenseLeaseController.url(options),
    method: 'post',
})

LicenseLeaseController.form = LicenseLeaseControllerForm

export default LicenseLeaseController