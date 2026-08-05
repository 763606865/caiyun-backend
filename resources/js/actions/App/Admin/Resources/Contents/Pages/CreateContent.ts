import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
const CreateContent = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateContent.url(options),
    method: 'get',
})

CreateContent.definition = {
    methods: ["get","head"],
    url: '/admin/contents/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
CreateContent.url = (options?: RouteQueryOptions) => {
    return CreateContent.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
CreateContent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateContent.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
CreateContent.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateContent.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
const CreateContentForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateContent.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
CreateContentForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateContent.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Contents\Pages\CreateContent::__invoke
* @see app/Admin/Resources/Contents/Pages/CreateContent.php:7
* @route '/admin/contents/create'
*/
CreateContentForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateContent.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateContent.form = CreateContentForm

export default CreateContent