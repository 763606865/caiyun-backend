import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
const ListAdminUsers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListAdminUsers.url(options),
    method: 'get',
})

ListAdminUsers.definition = {
    methods: ["get","head"],
    url: '/admin/admin-users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
ListAdminUsers.url = (options?: RouteQueryOptions) => {
    return ListAdminUsers.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
ListAdminUsers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListAdminUsers.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
ListAdminUsers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListAdminUsers.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
const ListAdminUsersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminUsers.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
ListAdminUsersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminUsers.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\ListAdminUsers::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/ListAdminUsers.php:7
* @route '/admin/admin-users'
*/
ListAdminUsersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListAdminUsers.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListAdminUsers.form = ListAdminUsersForm

export default ListAdminUsers