import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
const ListCustomerRequirements = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListCustomerRequirements.url(options),
    method: 'get',
})

ListCustomerRequirements.definition = {
    methods: ["get","head"],
    url: '/admin/customer-requirements',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
ListCustomerRequirements.url = (options?: RouteQueryOptions) => {
    return ListCustomerRequirements.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
ListCustomerRequirements.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListCustomerRequirements.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
ListCustomerRequirements.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListCustomerRequirements.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
const ListCustomerRequirementsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCustomerRequirements.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
ListCustomerRequirementsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCustomerRequirements.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\ListCustomerRequirements::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/ListCustomerRequirements.php:7
* @route '/admin/customer-requirements'
*/
ListCustomerRequirementsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCustomerRequirements.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListCustomerRequirements.form = ListCustomerRequirementsForm

export default ListCustomerRequirements