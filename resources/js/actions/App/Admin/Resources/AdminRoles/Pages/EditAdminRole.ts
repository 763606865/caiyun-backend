import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
const EditAdminRole = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditAdminRole.url(args, options),
    method: 'get',
})

EditAdminRole.definition = {
    methods: ["get","head"],
    url: '/admin/admin-roles/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
EditAdminRole.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditAdminRole.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
EditAdminRole.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditAdminRole.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
EditAdminRole.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditAdminRole.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
const EditAdminRoleForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminRole.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
EditAdminRoleForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminRole.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\EditAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/EditAdminRole.php:7
* @route '/admin/admin-roles/{record}/edit'
*/
EditAdminRoleForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminRole.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditAdminRole.form = EditAdminRoleForm

export default EditAdminRole