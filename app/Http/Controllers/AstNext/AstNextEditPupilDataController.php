<?php

namespace App\Http\Controllers\AstNext;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dbschools\Model_arr_year;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_ass_rawdata;
use App\Models\Dbschools\Model_ass_score;
use App\Models\Dbschools\Model_ass_tracking;
use App\Models\Dbschools\Model_cas_class_message;
use App\Models\Dbschools\Model_cas_pupil_signpost;
use App\Models\Dbschools\Model_log_login;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_rep_single;
use App\Models\Dbschools\Model_rep_single_pdf;
use App\Models\Dbschools\Model_rep_single_review;
use App\Models\Dbschools\Model_school_table_exist;
use App\Models\Dbschools\Model_arr_subschools;
use App\Services\AstNextEditPupilDataServiceProvider;
use App\Services\AstNextServiceProvider;


class AstNextEditPupilDataController extends Controller
{
    public function __construct()
    {
        $this->common_data = Config('constants.language_page_id.common_data');
        $this->cohort_data_side_bar_options = Config('constants.language_page_id.cohort_data_side_bar_options');
        $this->editPupilData = new AstNextEditPupilDataServiceProvider();
        $this->commonServiceProvider = new AstNextServiceProvider();
        $this->edit_pupil_data = Config('constants.language_page_id.edit_pupil_data');
        $this->edit_pupil_tile = Config('constants.language_page_id.edit_pupil_tile');
        $this->population_model = new Model_population();
        $this->export_as_tracking_score = Config('constants.language_page_id.export_as_tracking_score');
        $this->schoolTableExist_model = new Model_school_table_exist();
        $this->arr_year_model = new Model_arr_year();
        $this->arr_subschools_model = new Model_arr_subschools();
     }

    public function fetchPupilRecords(Request $request, $level) {
        
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $data = $this->editPupilData->getPupilData($request, $level);
        
        $final_array['data'] = $data;
        $final_array['message'] = "Data get successfully.";
        $final_array = json_encode($final_array, JSON_INVALID_UTF8_IGNORE);
        return $final_array;
    }
    
    public function addPupil(Request $request) {
        $final_array = [];
        $your_level = $request->get('level');
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);

        $lang = myLangId();
        $page = $this->edit_pupil_data;
        $language_wise_items = fetchLanguageText($lang, $page);

        $lang_text = myLangId();
        $page_text = $this->edit_pupil_tile;
        $language_wise_items_text = fetchLanguageText($lang_text, $page_text);

        $msgArr = array(
            'mis_id' => $language_wise_items_text['hlp.53'],
            'anon_name' => $language_wise_items_text['st.69'],
            'gender' => $language_wise_items_text['hlp.55'],
            'dob' => $language_wise_items_text['hlp.56'],
            'username' => $language_wise_items_text['hlp.57'],
            'password' => $language_wise_items_text['hlp.59'],
            'year' => $language_wise_items_text['hlp.60']
        );
        $validator = $this->commonServiceProvider->getRequiredData($request, $msgArr);

        if (!empty($validator)) {
            $final_array = $validator;
            return $final_array;
        }

        $getData = $this->editPupilData->addPupilData($request, $language_wise_items, $language_wise_items_text);

        $final_array = $getData;
        return $final_array;
    }
    
    public function editPupilView(Request $request, $id, $level) {
        ini_set('max_execution_time', 180);
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        
        $getData = $this->editPupilData->getPupilDetails($request, $id, $level);

        $final_array = $getData;
        $final_array = json_encode($final_array, JSON_INVALID_UTF8_IGNORE);
        return $final_array;
    }

    public function getSponsoredPupil(Request $request) {
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        
        $getData = $this->editPupilData->getSponsoredPupil($request);

        $final_array = $getData;
        $final_array = json_encode($final_array, JSON_INVALID_UTF8_IGNORE);
        return $final_array;
    }

    public function editPupil(Request $request)
    {
        $final_array = [];
        $id = $request->get('pupil_id');
        $your_level = $request->get('level');
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);

        $lang_text = myLangId();
        $page_text = $this->edit_pupil_tile;
        $language_wise_items_text = fetchLanguageText($lang_text, $page_text);

        $msgArr = array(
            'mis_id' => $language_wise_items_text['hlp.53'],
            'anon_name' => $language_wise_items_text['st.69'],
            'gender' => $language_wise_items_text['hlp.55'],
            'dob' => $language_wise_items_text['hlp.56'],
            'username' => $language_wise_items_text['hlp.57'],
            'password' => $language_wise_items_text['hlp.59'],
            'year' => $language_wise_items_text['hlp.60']
        );
        $validator = $this->commonServiceProvider->getRequiredData($request, $msgArr);

        if (!empty($validator)) {
            $final_array = $validator;
            return $final_array;
        }

        $getData = $this->editPupilData->editPupilData($request, $language_wise_items_text);

        $final_array = $getData;
        return $final_array;
    }
    public function deletePupil(Request $request)
    {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $data = explode(',', request()->get('id'));
        
        foreach ($data as $id) {
            $checkPupil = $this->population_model->get($id);
            if(empty($checkPupil) && !isset($checkPupil)){
                $final_array['message'] = $id . " students has been not found.";
                return $final_array;
            }
        }
        
        $resutlData = $this->editPupilData->deletePupils($request, $data);
        $final_array = $resutlData;
        return $final_array;
    }

    public function exportPupildataCsv(Request $request, $level)
    {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $getData = $this->editPupilData->exportPupilData($request, $level);
        $final_array = $getData;
        $final_array = json_encode($final_array, JSON_INVALID_UTF8_IGNORE);
        return $final_array;
    }
    public function getSubSchools(Request $request) {
        $final_array = [];
        $school_id = $request->get('school_id');
        $make_school_connection = dbSchool($school_id);
        $get_subschools = $this->arr_subschools_model->getAllSubschools();
        $get_subschools = str_replace('0', "No Campus", array_values($get_subschools));
        $final_array['campuses'] = $get_subschools;
        return $final_array;
    }
}
