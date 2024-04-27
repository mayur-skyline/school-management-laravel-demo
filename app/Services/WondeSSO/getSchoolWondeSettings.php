<?php

namespace App\Services\WondeSSO;

use Illuminate\Http\Request;
use App\Models\Dbglobal\Model_wonde_schools;
use Illuminate\Support\Facades\Log;

class getSchoolWondeSettings {

    public function index($school_wonde_id) {
        
        try {
           return ( new Model_wonde_schools )->wondeSchoolData( $school_wonde_id );
        }catch(Exception $ex){
            Log::info($ex);
        }
    }
}