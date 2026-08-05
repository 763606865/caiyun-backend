import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
const EditTag = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditTag.url(args, options),
    method: 'get',
})

EditTag.definition = {
    methods: ["get","head"],
    url: '/admin/tags/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
EditTag.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return EditTag.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
EditTag.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditTag.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
EditTag.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditTag.url(args, options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
const EditTagForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditTag.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
EditTagForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditTag.url(args, options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\EditTag::__invoke
* @see app/Admin/Resources/Tags/Pages/EditTag.php:7
* @route '/admin/tags/{record}/edit'
*/
EditTagForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditTag.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditTag.form = EditTagForm

export default EditTag