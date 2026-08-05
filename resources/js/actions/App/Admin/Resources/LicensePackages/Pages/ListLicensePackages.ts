import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
const ListLicensePackages = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListLicensePackages.url(options),
    method: 'get',
})

ListLicensePackages.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
ListLicensePackages.url = (options?: RouteQueryOptions) => {
    return ListLicensePackages.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
ListLicensePackages.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListLicensePackages.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
ListLicensePackages.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListLicensePackages.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
const ListLicensePackagesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListLicensePackages.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
ListLicensePackagesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListLicensePackages.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
ListLicensePackagesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListLicensePackages.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListLicensePackages.form = ListLicensePackagesForm

export default ListLicensePackages