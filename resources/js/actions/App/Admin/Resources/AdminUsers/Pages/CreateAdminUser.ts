import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
const CreateAdminUser = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateAdminUser.url(options),
    method: 'get',
})

CreateAdminUser.definition = {
    methods: ["get","head"],
    url: '/admin/admin-users/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
CreateAdminUser.url = (options?: RouteQueryOptions) => {
    return CreateAdminUser.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
CreateAdminUser.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateAdminUser.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
CreateAdminUser.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateAdminUser.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
const CreateAdminUserForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminUser.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
CreateAdminUserForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminUser.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\AdminUsers\Pages\CreateAdminUser::__invoke
* @see app/Admin/Resources/AdminUsers/Pages/CreateAdminUser.php:7
* @route '/admin/admin-users/create'
*/
CreateAdminUserForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateAdminUser.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateAdminUser.form = CreateAdminUserForm

export default CreateAdminUser