import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
const EditContent = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditContent.url(args, options),
    method: 'get',
})

EditContent.definition = {
    methods: ["get","head"],
    url: '/admin/contents/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
EditContent.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditContent.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
EditContent.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditContent.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
EditContent.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditContent.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
const EditContentForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditContent.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
EditContentForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditContent.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\EditContent::__invoke
* @see app/Admin/Resources/Contents/Pages/EditContent.php:7
* @route '/admin/contents/{record}/edit'
*/
EditContentForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditContent.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditContent.form = EditContentForm

export default EditContent