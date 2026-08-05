import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\CustomerRequirementController::store
* @see app/Api/Controllers/CustomerRequirementController.php:30
* @route '/api/customer-requirements'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/customer-requirements',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\CustomerRequirementController::store
* @see app/Api/Controllers/CustomerRequirementController.php:30
* @route '/api/customer-requirements'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\CustomerRequirementController::store
* @see app/Api/Controllers/CustomerRequirementController.php:30
* @route '/api/customer-requirements'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\CustomerRequirementController::store
* @see app/Api/Controllers/CustomerRequirementController.php:30
* @route '/api/customer-requirements'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\CustomerRequirementController::store
* @see app/Api/Controllers/CustomerRequirementController.php:30
* @route '/api/customer-requirements'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const CustomerRequirementController = { store }

export default CustomerRequirementController