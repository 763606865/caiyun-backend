import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
const CreateCustomerRequirement = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateCustomerRequirement.url(options),
    method: 'get',
})

CreateCustomerRequirement.definition = {
    methods: ["get","head"],
    url: '/admin/customer-requirements/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
CreateCustomerRequirement.url = (options?: RouteQueryOptions) => {
    return CreateCustomerRequirement.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
CreateCustomerRequirement.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateCustomerRequirement.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
CreateCustomerRequirement.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateCustomerRequirement.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
const CreateCustomerRequirementForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateCustomerRequirement.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
CreateCustomerRequirementForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateCustomerRequirement.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\CreateCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/CreateCustomerRequirement.php:7
* @route '/admin/customer-requirements/create'
*/
CreateCustomerRequirementForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateCustomerRequirement.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateCustomerRequirement.form = CreateCustomerRequirementForm

export default CreateCustomerRequirement