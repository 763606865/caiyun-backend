import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
const CreateMedia = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateMedia.url(options),
    method: 'get',
})

CreateMedia.definition = {
    methods: ["get","head"],
    url: '/admin/media/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
CreateMedia.url = (options?: RouteQueryOptions) => {
    return CreateMedia.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
CreateMedia.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
CreateMedia.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateMedia.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
const CreateMediaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
CreateMediaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateMedia.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Media\Pages\CreateMedia::__invoke
* @see app/Admin/Resources/Media/Pages/CreateMedia.php:7
* @route '/admin/media/create'
*/
CreateMediaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateMedia.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateMedia.form = CreateMediaForm

export default CreateMedia