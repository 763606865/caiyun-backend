import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\FileController::upload
* @see app/Api/Controllers/FileController.php:18
* @route '/api/files/upload'
*/
export const upload = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/api/files/upload',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\FileController::upload
* @see app/Api/Controllers/FileController.php:18
* @route '/api/files/upload'
*/
upload.url = (options?: RouteQueryOptions) => {
    return upload.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\FileController::upload
* @see app/Api/Controllers/FileController.php:18
* @route '/api/files/upload'
*/
upload.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\FileController::upload
* @see app/Api/Controllers/FileController.php:18
* @route '/api/files/upload'
*/
const uploadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\FileController::upload
* @see app/Api/Controllers/FileController.php:18
* @route '/api/files/upload'
*/
uploadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(options),
    method: 'post',
})

upload.form = uploadForm

const FileController = { upload }

export default FileController