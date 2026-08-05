import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
const ListCategories = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListCategories.url(options),
    method: 'get',
})

ListCategories.definition = {
    methods: ["get","head"],
    url: '/admin/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
ListCategories.url = (options?: RouteQueryOptions) => {
    return ListCategories.definition.url + queryParams(options)
}

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
ListCategories.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListCategories.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
ListCategories.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListCategories.url(options),
    method: 'head',
})

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
const ListCategoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCategories.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
ListCategoriesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCategories.url(options),
    method: 'get',
})

/**
* @see \App\Admin\Resources\Categories\Pages\ListCategories::__invoke
* @see app/Admin/Resources/Categories/Pages/ListCategories.php:7
* @route '/admin/categories'
*/
ListCategoriesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListCategories.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListCategories.form = ListCategoriesForm

export default ListCategories