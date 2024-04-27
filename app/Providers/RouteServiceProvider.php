<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot() {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map() {

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapAstrackingAppRoutes();

        $this->mapPortalRoutes();

        $this->mapSgaRoutes();

        $this->mapAstNextRoutes();

        $this->mapSetupRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes() {
        Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes() {
        Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
    }

    protected function mapAstrackingAppRoutes() {
        Route::group([
            'prefix' => 'astracking/pupil',
            'namespace' => $this->namespace,
                ], function ($router) {
            require base_path('routes/astracking/pupil.php');
        });
    }

    /**
     * Custom routes for Portal module
     */
    protected function mapPortalRoutes() {
        Route::prefix('portal')
                ->namespace($this->namespace)
                ->group(base_path('routes/portal.php'));
    }

    /**
     * Custom routes for SGA module
     */
    protected function mapSgaRoutes() {
        Route::prefix('sga')
                ->namespace($this->namespace)
                ->group(base_path('routes/sga.php'));
    }

    /**
     * Custom routes for AstNext module
     */
    protected function mapAstNextRoutes() {
        Route::prefix('api-astnext')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-astnext.php'));
    }

    /**
     * Custom routes for New Year SetUp module
     */
    protected function mapSetupRoutes() {
        Route::prefix('api-setup')
                ->namespace($this->namespace)
                ->group(base_path('routes/api-setup.php'));
    }

    

}
