<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use App\Models\Dbportal\Model_sga_auth;
use App\Models\Dbportal\Model_permissions_matrix;

class checkPermission {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function __construct() {
        $this->sga_auth_model = new Model_sga_auth();
        $this->permissions_matrix_model = new Model_permissions_matrix();
    }

    public function handle($request, Closure $next) {
        $response = array();

        $matrix_array = $matrix_user = array();

        $make_portal_connection = dbPortal();
        $cookie_portal = Cookie::get('portal');
        $login_data = explode("-", $cookie_portal);

        $sga_auth_condition['sga_password'] = $login_data[0];
        $get_login_user = $this->sga_auth_model->getSgaAuthByCondition($sga_auth_condition);
        unset($sga_auth_condition);

        $matrix_table = getTableDescribe('permissions_matrix');
        foreach ($matrix_table as $matrix_table_key => $matrix_table_value) {
            $matrix_array[] = $matrix_table_value->Field;
        }
        unset($matrix_array[0], $matrix_array[1]);
        $matrix_array = array_values($matrix_array);

        $permission_condition['user'] = $get_login_user['user_email'];
        $matrix_data = $this->permissions_matrix_model->getPermissionsMatrixByCondition($permission_condition);
        unset($permission_condition);

        for ($i = 0; $i < count($matrix_array); $i++) {
            $matrix_user[$get_login_user['user_email']][] = $matrix_data[$matrix_array[$i]];
        }
        if (empty($get_login_user['user_email']) || !isset($matrix_user[$get_login_user['user_email']][0]) || empty($matrix_user[$get_login_user['user_email']][0]) || $matrix_user[$get_login_user['user_email']][0] == '0') {
            return redirect('portal/check-permission');
        }
        return $next($request);
    }

}
