import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
const CreateAdminRole = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateAdminRole.url(options),
    method: 'get',
})

CreateAdminRole.definition = {
    methods: ["get","head"],
    url: '/admin/admin-roles/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
CreateAdminRole.url = (options?: RouteQueryOptions) => {
    return CreateAdminRole.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
CreateAdminRole.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateAdminRole.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
CreateAdminRole.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateAdminRole.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
const CreateAdminRoleForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminRole.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
CreateAdminRoleForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminRole.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\CreateAdminRole::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/CreateAdminRole.php:7
* @route '/admin/admin-roles/create'
*/
CreateAdminRoleForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminRole.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateAdminRole.form = CreateAdminRoleForm

export default CreateAdminRole