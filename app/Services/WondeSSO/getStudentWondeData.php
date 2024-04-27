<?php

namespace App\Services\WondeSSO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Dbglobal\Model_wonde_schools;
use Illuminate\Support\Facades\Log;
use Exception;

class getStudentWondeData {

    public function index($school_wonde_id, $student_wonde_id) {
        
        try {
            $multitoken = ( new Model_wonde_schools )->getMultiToken();
            if( !$multitoken ) {
                return null;
            }
            $url = "https://api.wonde.com/v1.0/schools/$school_wonde_id/students/$student_wonde_id?include=education_details,extended_details&extra_ids=true";
            $response = Http::withHeaders([
                'Authorization' => "Bearer $multitoken->read"
            ])->get($url);
            if( $response->status() == 404) {
                throw new Exception("School not Found", 404);
            }
            return $response->json();
        }catch(Exception $ex){
            throw new Exception($ex->getMessage(), 404);
        }
    }
}