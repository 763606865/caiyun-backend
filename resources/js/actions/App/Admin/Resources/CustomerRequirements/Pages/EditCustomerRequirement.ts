import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
const EditCustomerRequirement = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditCustomerRequirement.url(args, options),
    method: 'get',
})

EditCustomerRequirement.definition = {
    methods: ["get","head"],
    url: '/admin/customer-requirements/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
EditCustomerRequirement.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditCustomerRequirement.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
EditCustomerRequirement.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditCustomerRequirement.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
EditCustomerRequirement.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditCustomerRequirement.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
const EditCustomerRequirementForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditCustomerRequirement.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
EditCustomerRequirementForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditCustomerRequirement.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\CustomerRequirements\Pages\EditCustomerRequirement::__invoke
* @see app/Admin/Resources/CustomerRequirements/Pages/EditCustomerRequirement.php:7
* @route '/admin/customer-requirements/{record}/edit'
*/
EditCustomerRequirementForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditCustomerRequirement.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditCustomerRequirement.form = EditCustomerRequirementForm

export default EditCustomerRequirement