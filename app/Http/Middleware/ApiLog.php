<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Dbglobal\Ast_app\V2_0\Model_api_log;

class ApiLog {

    public function __construct() {
        $this->api_login_model = new Model_api_log();
    }

    public function handle($request, Closure $next) {
        $route = $request->segments();
        $route = end($route);
        $header = json_encode($request->header());
        $param = json_encode($request->all());
        $created = date('YmdHis');
        $url = $request->url();

        $this->api_login_model->saveLogData($param, $header, $created,$url);
        return $next($request);
    }

}
