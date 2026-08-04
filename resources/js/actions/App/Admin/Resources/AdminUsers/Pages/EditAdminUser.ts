import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
const EditAdminUser = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditAdminUser.url(args, options),
    method: 'get',
})

EditAdminUser.definition = {
    methods: ["get","head"],
    url: '/admin/admin-users/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
EditAdminUser.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditAdminUser.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
EditAdminUser.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditAdminUser.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
EditAdminUser.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditAdminUser.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
const EditAdminUserForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminUser.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
EditAdminUserForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminUser.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\EditAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/EditAdminUser.php:7
* @route '/admin/admin-users/{record}/edit'
*/
EditAdminUserForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditAdminUser.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditAdminUser.form = EditAdminUserForm

export default EditAdminUser