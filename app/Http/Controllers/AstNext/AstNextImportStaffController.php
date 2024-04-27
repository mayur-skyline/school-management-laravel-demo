<?php

namespace App\Http\Controllers\AstNext;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dbglobal\Model_wonde_schools;
use App\Models\Dbschools\Model_population;
use App\Models\Dbglobal\Model_import_staff_error;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_temp_csv;
use App\Services\AstNextWondeImportStaffServiceProvider;
use App\Services\AstNextImportStaffDataServiceProvider;
use App\Services\AstNextServiceProvider;

class AstNextImportStaffController extends Controller {

    public function __construct() {
        $this->WondeImportStaffServiceProvider = new AstNextWondeImportStaffServiceProvider();
        $this->wonde_school_model = new Model_wonde_schools();
        $this->wonde_import = Config('constants.language_page_id.wonde_import');
        $this->population_model = new Model_population();
        $this->import_staff_error_model = new Model_import_staff_error();
        $this->import_staff_data = Config('constants.language_page_id.import_staff_data');
        $this->dat_schools_model = new Model_dat_schools();
        $this->common_data = Config('constants.language_page_id.common_data');
        $this->importStaffData = new AstNextImportStaffDataServiceProvider();
        $this->temp_csv_model = new Model_temp_csv();
    }

    public function wondeStaffImport(Request $request) {
        $school_id = $request->get('school_id');
        $request_data = $request->all();
        $sample_data = $this->WondeImportStaffServiceProvider->wondeStaffImport($school_id, $request_data);
        return $sample_data;
    }

    public function showWondeStaffCompareData(Request $request) {
        $language_wise_wonde_import = fetchLanguageText(myLangId(), $this->wonde_import);
        $school_id = $request->school_id;
        $make_schoool_connection = DbSchool($school_id);
        $sample_data = $this->WondeImportStaffServiceProvider->checkwondeStaffCompareData($request, $language_wise_wonde_import);
        return $sample_data;
    }

    public function selectWondeStaffData(Request $request) {
        $school_id = $request->school_id;
        $checkmisarrdata = $request->checkmisarrdata;
        $wondestaffdata = $this->WondeImportStaffServiceProvider->checkselectwondestaffdata($school_id, $request);

        return $wondestaffdata;
    }

    public function wondeStaffSelectProcess(Request $request) {
        $misarrdata = json_decode($request->misiid_array, true);
        $school_id = $request->school_id;
        $make_schoool_connection = DbSchool($school_id);
        $wonde_staff_data = $this->WondeImportStaffServiceProvider->getWondeStaffSelectProcess($misarrdata);

        return $wonde_staff_data;
    }

    public function wondeStaffImportData(Request $request) {

        $school_id = $request->school_id;
        $make_schoool_connection = DbSchool($school_id);
        $wonde_staff_import_data = $this->WondeImportStaffServiceProvider->getWondeStaffImportData($school_id, $request);

        return $wonde_staff_import_data;
    }

    public function checkWondeDuplicateStaffUsername(Request $request) {
        $school_id = $request->school_id;
        $make_schoool_connection = DbSchool($school_id);

        $wonde_staff_import_data = $this->WondeImportStaffServiceProvider->getCheckWondeDuplicateStaffUsername($request);

        return $wonde_staff_import_data;
    }

    public function wondeStaffImportProcess(Request $request) {
        $return_array = [];
        $select_mis = explode(",", $request->staff_select);
        $language_wise_wonde_import = fetchLanguageText(myLangId(), $this->wonde_import);

        $school_id = $request->school_id;
        $make_schoool_connection = DbSchool($school_id);

        $lang = myLangId();
        $page = $this->import_staff_data;
        $language_wise_items = fetchLanguageText($lang, $page);

        $checknamecode = $this->dat_schools_model->checkNameCode($school_id);
    
        if (isset($checknamecode['wonde_api']) && $checknamecode['wonde_api'] == "y") {
            return $return_array = array("data" => ['language_wise_wonde_import' => $language_wise_wonde_import, 'language_wise_items' => $language_wise_items, 'select_mis' => $select_mis, 'send_email' => $request->send_email]);
        }
        return $return_array = ['data' => []];
    }
    
    //Import staff data using CSV
    
    public function intialCheck(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->step1IntialCheck($request, $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function uploadCsv(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->step2UploadSelectedCsv($request);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffProfile(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffStep3StaffProfile($request, $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffNewMatch(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffStep3Setnewmatch($request);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffCheckTemp(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffStep3Checktemp($request, $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function editCheckStaffName(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->editCheckingStaff($request, $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffEditChangesData(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffEditChangeData($request);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffCSVDeleteRow(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $delrow = $request->get('row');
        if(empty($delrow)){
            $final_array['message'] = "row not found";  
            return $final_array;
        }
        $delid = $this->temp_csv_model->delRow($delrow);
        $final_array['msg'] = 'success';
        return $final_array;
    }
    
    public function staffNameMatching(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffNameMatching($request , $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function matchUpdateData(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        $tmpid = $request->get('tmp_id');
        $match = $request->get('match');

        if (isset($tmpid) && !empty($match)) {
            $updateArr = array_combine($tmpid, $match);
            foreach ($updateArr as $updatekey => $updateval) {
                $tmp_id = $updatekey;
                $matchupdatedata['match'] = $updateval;
                $updatedata = $this->temp_csv_model->updateTempCsv($tmp_id, $matchupdatedata);
            }
            $res = "success";
        } else {
            $res = "error";
        }
        $final_array['msg'] = $res;
        return $final_array;
    }
    
    public function staffDisplayNameMatching(Request $request, $option) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->staffDisplayNameMatching($request, $option);
        $final_array = $getData;
        return $final_array;
    }
    
    public function staffFinalSaveData(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_schoool_connection = dbSchool($school_id);
        
        $getData = $this->importStaffData->finalSaveData($request);
        $final_array = $getData;
        return $final_array;
    }
}