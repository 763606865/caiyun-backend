import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
const ListSystemSettings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListSystemSettings.url(options),
    method: 'get',
})

ListSystemSettings.definition = {
    methods: ["get","head"],
    url: '/admin/system-settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
ListSystemSettings.url = (options?: RouteQueryOptions) => {
    return ListSystemSettings.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
ListSystemSettings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListSystemSettings.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
ListSystemSettings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListSystemSettings.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
const ListSystemSettingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSystemSettings.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
ListSystemSettingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSystemSettings.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\ListSystemSettings::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/ListSystemSettings.php:7
* @route '/admin/system-settings'
*/
ListSystemSettingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListSystemSettings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListSystemSettings.form = ListSystemSettingsForm

export default ListSystemSettings