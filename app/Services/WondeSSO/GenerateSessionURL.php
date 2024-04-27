<?php

namespace App\Services\WondeSSO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Dbschools\Model_population;
use Illuminate\Support\Facades\Log as L;

class GenerateSessionURL
{
    public function index($school_id, $email) {
        if( !$school_id ) {
            throw new \Exception("Unknown School ID");
        }
        DbSchool( $school_id );
        $staff = ( new Model_population )->getStaffDetails( strtolower($email ?? "") );
        if( !$staff ) {
            throw new \Exception("Staff not Found");
        }
        $concat_id = $staff->id.'-'.$school_id;
        $encrypted_user_id =  encrypt_decrypt('encrypt', $concat_id );
        setuserIdEncryptedValue( $encrypted_user_id );
        return [ "url" => "/auth-redirect/".$encrypted_user_id, "staff" => $staff ];
    }
}
  
