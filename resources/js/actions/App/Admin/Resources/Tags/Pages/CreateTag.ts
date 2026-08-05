import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
const CreateTag = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateTag.url(options),
    method: 'get',
})

CreateTag.definition = {
    methods: ["get","head"],
    url: '/admin/tags/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
CreateTag.url = (options?: RouteQueryOptions) => {
    return CreateTag.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
CreateTag.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateTag.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
CreateTag.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateTag.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
const CreateTagForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateTag.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
CreateTagForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateTag.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Tags\Pages\CreateTag::__invoke
* @see app/Admin/Resources/Tags/Pages/CreateTag.php:7
* @route '/admin/tags/create'
*/
CreateTagForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateTag.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateTag.form = CreateTagForm

export default CreateTag