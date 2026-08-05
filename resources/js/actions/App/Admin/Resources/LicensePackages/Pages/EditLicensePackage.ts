import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
const EditLicensePackage = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditLicensePackage.url(args, options),
    method: 'get',
})

EditLicensePackage.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
EditLicensePackage.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditLicensePackage.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
EditLicensePackage.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditLicensePackage.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
EditLicensePackage.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditLicensePackage.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
const EditLicensePackageForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditLicensePackage.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
EditLicensePackageForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditLicensePackage.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
EditLicensePackageForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditLicensePackage.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditLicensePackage.form = EditLicensePackageForm

export default EditLicensePackage