import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
const CreateSystemSetting = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateSystemSetting.url(options),
    method: 'get',
})

CreateSystemSetting.definition = {
    methods: ["get","head"],
    url: '/admin/system-settings/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
CreateSystemSetting.url = (options?: RouteQueryOptions) => {
    return CreateSystemSetting.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
CreateSystemSetting.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateSystemSetting.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
CreateSystemSetting.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateSystemSetting.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
const CreateSystemSettingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSystemSetting.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
CreateSystemSettingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSystemSetting.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\CreateSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/CreateSystemSetting.php:7
* @route '/admin/system-settings/create'
*/
CreateSystemSettingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateSystemSetting.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateSystemSetting.form = CreateSystemSettingForm

export default CreateSystemSetting