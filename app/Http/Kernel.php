<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\CheckRedisConnection::class,
        \App\Http\Middleware\NoCache::class,
        \Illuminate\Session\Middleware\StartSession::class,
//        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\SecureHeadersMiddleware::class,
        \Fruitcake\Cors\HandleCors::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            //\Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\RedirectWWWToNonWWW::class

        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'authLogin' => \App\Http\Middleware\authLogin::class,
        'otherLanguage' => \App\Http\Middleware\otherLanguage::class,
        'level' => \App\Http\Middleware\checkLevel::class,
        'package' => \App\Http\Middleware\checkPackage::class,
        'status2' => \App\Http\Middleware\status2::class,
        'status3' => \App\Http\Middleware\status3::class,
        'status4' => \App\Http\Middleware\status4::class,
        'checkAuthToken' => \App\Http\Middleware\CheckAuthToken::class,
        'checkUserLogin' => \App\Http\Middleware\CheckUserLogin::class,
        'portal' => \App\Http\Middleware\portal::class,
        'authPortalLogin' => \App\Http\Middleware\authPortalLogin::class,
        'authSgaLogin' => \App\Http\Middleware\authSgaLogin::class,
        'apiLog' => \App\Http\Middleware\ApiLog::class,
        'checkPermission' => \App\Http\Middleware\checkPermission::class,
        'checkSgaPermission' => \App\Http\Middleware\checkSgaPermission::class,
        'httpsProtocol' => \App\Http\Middleware\httpsProtocol::class,
        'checkredis' => \App\Http\Middleware\CheckRedisConnection::class,
        'nocache' => \App\Http\Middleware\NoCache::class,
        'xss' => \App\Http\Middleware\xss::class,
        'header' => \App\Http\Middleware\SecureHeadersMiddleware::class,
        'connection' => \App\Http\Middleware\DatabaseConnection::class,
        'apiResponseType' => \App\Http\Middleware\ApiResponseType::class,
        'school_conn' => \App\Http\Middleware\SchoolConn::class,
        'http_referrer' => \App\Http\Middleware\HTTPReferrerToSession::class,
        'wonde' => \App\Http\Middleware\WondeAccess::class,
        'globalConnection' => \App\Http\Middleware\GlobalDBConnection::class,
        'filterChecker' => \App\Http\Middleware\FilterChecker::class,
	'platformRouting' => \App\Http\Middleware\PlatformRouting::class,
        'webhook' => \App\Http\Middleware\Webhook::class,
        'manageSession' =>  \App\Http\Middleware\ManageSession::class,
        'istokenAvailable' => \App\Http\Middleware\IstokenAvailable::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

}
