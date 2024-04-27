<?php
namespace App\Services\FactorAuthentication;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_factor_auth_token;
use Carbon\Carbon;
use App\Util\Mailing\Email;

class Manage2FAToken
{
    public function __construct()
    {
    }

    public function generateToken( $user ) {
        $token = random_int(100000, 999999);
        $data = ( new Model_factor_auth_token )->saveToken( $user->id, $token );
        return ( new Email )->FactorAuthMail( strrev( $user->username ), "Your Authentication Code is $token ");
    }

    public function verifyToken( $user_id, $code ) {
        $data =  ( new Model_factor_auth_token )->getToken( $user_id, $code );
        if( !$data ) return [ 'message' => "That wasn’t right", "success" => false ];
        $created_at = strtotime( $data->created_at );
        $now = strtotime(Carbon::now());
        $diff_minutes = round(abs($now - $created_at) / 60,2);
        if( $diff_minutes <= 5 ) {
            return [ 'message' => "Token Valid", "success" => true ];
        }
        
        return [ "message" => "That code has expired! Click resend to get another", "success" => false  ];
        
    }

    public function removeToken( $user_id, $code ) {
        return Model_factor_auth_token::where(['user_id' => $user_id, 'token' => $code ])->delete();
    }

    public function removeAllUserToken( $user_id ) {
        return Model_factor_auth_token::where(['user_id' => $user_id ])->delete();
    }

    

}
