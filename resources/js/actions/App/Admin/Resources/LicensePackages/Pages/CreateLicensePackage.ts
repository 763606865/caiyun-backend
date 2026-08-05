import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
const CreateLicensePackage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateLicensePackage.url(options),
    method: 'get',
})

CreateLicensePackage.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
CreateLicensePackage.url = (options?: RouteQueryOptions) => {
    return CreateLicensePackage.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
CreateLicensePackage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateLicensePackage.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
CreateLicensePackage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateLicensePackage.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
const CreateLicensePackageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateLicensePackage.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
CreateLicensePackageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateLicensePackage.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
CreateLicensePackageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateLicensePackage.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateLicensePackage.form = CreateLicensePackageForm

export default CreateLicensePackage