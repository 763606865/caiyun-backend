import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
const EditSystemSetting = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditSystemSetting.url(args, options),
    method: 'get',
})

EditSystemSetting.definition = {
    methods: ["get","head"],
    url: '/admin/system-settings/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
EditSystemSetting.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditSystemSetting.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
EditSystemSetting.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditSystemSetting.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
EditSystemSetting.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditSystemSetting.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
const EditSystemSettingForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSystemSetting.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
EditSystemSettingForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSystemSetting.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\SystemSettings\Pages\EditSystemSetting::__invoke
* @see app/Admin/Resources/SystemSettings/Pages/EditSystemSetting.php:7
* @route '/admin/system-settings/{record}/edit'
*/
EditSystemSettingForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditSystemSetting.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditSystemSetting.form = EditSystemSettingForm

export default EditSystemSetting