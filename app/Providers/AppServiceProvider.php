<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url) {
        $ip = env('DB_GLOBAL_HOST');
        $ip_array = explode(',',$ip);
        
        foreach ($ip_array as $ip1) {
            for ($x = 1; $x <= 3; $x++) {
                DB::purge('mysql');
                try {
                    config(['database.connections.mysql.host' => $ip1]);
                    config(['app.host' => $ip1]);
                    putenv("DB_HOST=$ip1");
                    $connection = DB::connection()->getPdo();
                    
                    if (env('APP_ENV') == 'production') {
            //            $url->formatScheme('https');
                        $this->app['request']->server->set('HTTPS', true);
                    } else if(env('APP_ENV') == 'development'){
                        $this->app['request']->server->set('HTTPS', true);
                    } else {
                        $url->formatScheme('http');
                    }
                    return true;
                } catch (\Exception $e) {
                    sleep(0.3);
                }
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

}
