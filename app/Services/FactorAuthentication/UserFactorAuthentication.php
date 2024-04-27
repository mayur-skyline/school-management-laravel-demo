<?php
namespace App\Services\FactorAuthentication;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_user_factor_auth;
use DB;

class UserFactorAuthentication
{
    public function __construct()
    {
    }

    public function userFactorAuth( $status ) {
        return ( new Model_user_factor_auth )->userFactorAuth( $status );
    }

    public function getUserFactorAuth( $user_id ) {
        return ( new Model_user_factor_auth )->getUserFactorAuth( $user_id );
        
    }

}
