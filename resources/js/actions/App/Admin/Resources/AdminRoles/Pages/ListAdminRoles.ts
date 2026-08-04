import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
const ListAdminRoles = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListAdminRoles.url(options),
    method: 'get',
})

ListAdminRoles.definition = {
    methods: ["get","head"],
    url: '/admin/admin-roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
ListAdminRoles.url = (options?: RouteQueryOptions) => {
    return ListAdminRoles.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
ListAdminRoles.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListAdminRoles.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
ListAdminRoles.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListAdminRoles.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
const ListAdminRolesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminRoles.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
ListAdminRolesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminRoles.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminRoles\Pages\ListAdminRoles::__invoke
* @see app/Admin/Resources/AdminRoles/Pages/ListAdminRoles.php:7
* @route '/admin/admin-roles'
*/
ListAdminRolesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminRoles.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListAdminRoles.form = ListAdminRolesForm

export default ListAdminRoles