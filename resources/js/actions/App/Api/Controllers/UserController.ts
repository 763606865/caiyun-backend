import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\UserController::update
* @see app/Api/Controllers/UserController.php:24
* @route '/api/user'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/api/user',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Api\Controllers\UserController::update
* @see app/Api/Controllers/UserController.php:24
* @route '/api/user'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\UserController::update
* @see app/Api/Controllers/UserController.php:24
* @route '/api/user'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Api\Controllers\UserController::update
* @see app/Api/Controllers/UserController.php:24
* @route '/api/user'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Api\Controllers\UserController::update
* @see app/Api/Controllers/UserController.php:24
* @route '/api/user'
*/
updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Api\Controllers\UserController::sendPhoneCode
* @see app/Api/Controllers/UserController.php:52
* @route '/api/user/phone/sms-code'
*/
export const sendPhoneCode = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendPhoneCode.url(options),
    method: 'post',
})

sendPhoneCode.definition = {
    methods: ["post"],
    url: '/api/user/phone/sms-code',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\UserController::sendPhoneCode
* @see app/Api/Controllers/UserController.php:52
* @route '/api/user/phone/sms-code'
*/
sendPhoneCode.url = (options?: RouteQueryOptions) => {
    return sendPhoneCode.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\UserController::sendPhoneCode
* @see app/Api/Controllers/UserController.php:52
* @route '/api/user/phone/sms-code'
*/
sendPhoneCode.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendPhoneCode.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\UserController::sendPhoneCode
* @see app/Api/Controllers/UserController.php:52
* @route '/api/user/phone/sms-code'
*/
const sendPhoneCodeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendPhoneCode.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\UserController::sendPhoneCode
* @see app/Api/Controllers/UserController.php:52
* @route '/api/user/phone/sms-code'
*/
sendPhoneCodeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendPhoneCode.url(options),
    method: 'post',
})

sendPhoneCode.form = sendPhoneCodeForm

/**
* @see \App\Api\Controllers\UserController::updatePhone
* @see app/Api/Controllers/UserController.php:89
* @route '/api/user/phone'
*/
export const updatePhone = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePhone.url(options),
    method: 'patch',
})

updatePhone.definition = {
    methods: ["patch"],
    url: '/api/user/phone',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Api\Controllers\UserController::updatePhone
* @see app/Api/Controllers/UserController.php:89
* @route '/api/user/phone'
*/
updatePhone.url = (options?: RouteQueryOptions) => {
    return updatePhone.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\UserController::updatePhone
* @see app/Api/Controllers/UserController.php:89
* @route '/api/user/phone'
*/
updatePhone.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePhone.url(options),
    method: 'patch',
})

/**
* @see \App\Api\Controllers\UserController::updatePhone
* @see app/Api/Controllers/UserController.php:89
* @route '/api/user/phone'
*/
const updatePhoneForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePhone.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Api\Controllers\UserController::updatePhone
* @see app/Api/Controllers/UserController.php:89
* @route '/api/user/phone'
*/
updatePhoneForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePhone.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePhone.form = updatePhoneForm

const UserController = { update, sendPhoneCode, updatePhone }

export default UserController