import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Api\Controllers\AuthController::sendSmsCode
* @see app/Api/Controllers/AuthController.php:27
* @route '/api/auth/sms-code'
*/
export const sendSmsCode = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendSmsCode.url(options),
    method: 'post',
})

sendSmsCode.definition = {
    methods: ["post"],
    url: '/api/auth/sms-code',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\AuthController::sendSmsCode
* @see app/Api/Controllers/AuthController.php:27
* @route '/api/auth/sms-code'
*/
sendSmsCode.url = (options?: RouteQueryOptions) => {
    return sendSmsCode.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\AuthController::sendSmsCode
* @see app/Api/Controllers/AuthController.php:27
* @route '/api/auth/sms-code'
*/
sendSmsCode.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendSmsCode.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::sendSmsCode
* @see app/Api/Controllers/AuthController.php:27
* @route '/api/auth/sms-code'
*/
const sendSmsCodeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendSmsCode.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::sendSmsCode
* @see app/Api/Controllers/AuthController.php:27
* @route '/api/auth/sms-code'
*/
sendSmsCodeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: sendSmsCode.url(options),
    method: 'post',
})

sendSmsCode.form = sendSmsCodeForm

/**
* @see \App\Api\Controllers\AuthController::login
* @see app/Api/Controllers/AuthController.php:58
* @route '/api/auth/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/auth/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\AuthController::login
* @see app/Api/Controllers/AuthController.php:58
* @route '/api/auth/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\AuthController::login
* @see app/Api/Controllers/AuthController.php:58
* @route '/api/auth/login'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::login
* @see app/Api/Controllers/AuthController.php:58
* @route '/api/auth/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::login
* @see app/Api/Controllers/AuthController.php:58
* @route '/api/auth/login'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

/**
* @see \App\Api\Controllers\AuthController::wechatLogin
* @see app/Api/Controllers/AuthController.php:99
* @route '/api/auth/wechat/login'
*/
export const wechatLogin = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: wechatLogin.url(options),
    method: 'post',
})

wechatLogin.definition = {
    methods: ["post"],
    url: '/api/auth/wechat/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Api\Controllers\AuthController::wechatLogin
* @see app/Api/Controllers/AuthController.php:99
* @route '/api/auth/wechat/login'
*/
wechatLogin.url = (options?: RouteQueryOptions) => {
    return wechatLogin.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\AuthController::wechatLogin
* @see app/Api/Controllers/AuthController.php:99
* @route '/api/auth/wechat/login'
*/
wechatLogin.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: wechatLogin.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::wechatLogin
* @see app/Api/Controllers/AuthController.php:99
* @route '/api/auth/wechat/login'
*/
const wechatLoginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: wechatLogin.url(options),
    method: 'post',
})

/**
* @see \App\Api\Controllers\AuthController::wechatLogin
* @see app/Api/Controllers/AuthController.php:99
* @route '/api/auth/wechat/login'
*/
wechatLoginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: wechatLogin.url(options),
    method: 'post',
})

wechatLogin.form = wechatLoginForm

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
export const me = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: '/api/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
me.url = (options?: RouteQueryOptions) => {
    return me.definition.url + queryParams(options)
}

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
me.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
me.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(options),
    method: 'head',
})

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
const meForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
meForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Api\Controllers\AuthController::me
* @see app/Api/Controllers/AuthController.php:128
* @route '/api/me'
*/
meForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

me.form = meForm

const AuthController = { sendSmsCode, login, wechatLogin, me }

export default AuthController