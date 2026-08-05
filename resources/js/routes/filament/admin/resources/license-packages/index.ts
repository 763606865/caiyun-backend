import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\ListLicensePackages::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/ListLicensePackages.php:7
* @route '/admin/license-packages'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\CreateLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/CreateLicensePackage.php:7
* @route '/admin/license-packages/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
export const edit = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/license-packages/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
edit.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
edit.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
edit.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
const editForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
editForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\LicensePackages\Pages\EditLicensePackage::__invoke
* @see app/Admin/Resources/LicensePackages/Pages/EditLicensePackage.php:7
* @route '/admin/license-packages/{record}/edit'
*/
editForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

const licensePackages = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    edit: Object.assign(edit, edit),
}

export default licensePackages