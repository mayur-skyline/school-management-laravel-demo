<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use DateTime;
use App\Services\AstNextServiceProvider;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_arr_year;
use App\Services\AssessmentServiceProvider;
use App\Models\Dbglobal\Model_assessment_sections;
use App\Models\Dbglobal\Model_assessment_questions;
use App\Models\Dbglobal\Model_dat_product_module;
use App\Models\Dbschools\Model_tmp_store_browser_session;
use App\Models\Dbschools\Model_ass_rawdata;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbglobal\Model_assessment_builder;
use App\Models\Dbschools\Model_ass_tracking;
use Nette\Schema\Elements\Structure;
use Illuminate\Support\Arr;
use App\Libraries\Encdec;
use App\Models\Dbglobal\Model_assessment_setup;
use App\Models\Dbglobal\Model_dat_languages;
use App\Models\Dbschools\Model_ass_score;
use App\Models\Dbschools\Model_assessment_video;
use Illuminate\Support\Facades\Redis;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_del_records;
use App\Models\Dbschools\Model_ass_manipulated;
use App\Services\CohortServiceProvider;
use App\Libraries\AssessmentServiceLib;
use App\Libraries\URLService;
use App\Util\DBFilter\Filter;
use Illuminate\Support\Facades\DB;

class AstNextPupilAssessmentProvider
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->datSchools_model = new Model_dat_schools();
        $this->arrYear_model = new Model_arr_year();
        $this->assessmentServiceProvider = new AssessmentServiceProvider();
        $this->encdec = new Encdec();
        $this->assessment_sections_model = new Model_assessment_sections();
        $this->assessment_questions_model = new Model_assessment_questions();
        $this->tmpStoreBrowser_session_model = new Model_tmp_store_browser_session();
        $this->assMain_model = new Model_ass_main();
        $this->assRawdata_model = new Model_ass_rawdata();
        $this->assessment_builder_model = new Model_assessment_builder();
        $this->assTracking_model = new Model_ass_tracking();
        $this->dat_languages_model = new Model_dat_languages();
        $this->dat_school_model = new Model_dat_schools();
        $this->assessment_video_model = new Model_assessment_video();
        $this->assessment_setup_model = new Model_assessment_setup();
        $this->ass_score_model = new Model_ass_score();
        $this->population_model = new Model_population();
        $this->model_del_records = new Model_del_records();
        $this->model_dat_product_module = new Model_dat_product_module();
        $this->assManipulated_model = new Model_ass_manipulated();
        $this->CohortServiceProvider = new CohortServiceProvider();
    }

    public function delete($request, $id)
    {
        $school_id = $request->get('school_id');
        $reason = $request->get('type');
        $assmain_id = $id;
        $academic_year = $this->dat_school_model->SchoolAcademicYear($school_id);
        $checkAssMain = Model_ass_main::year($academic_year)->where('id', $assmain_id)->orderBy('id', 'desc')->first();
        if (!$checkAssMain) {
            return response()->json(['message' => 'Assessment not found']);
        }
        $fire_store_check = Model_ass_main::year($academic_year)->select('firestore_id')->where('id', $assmain_id)->orderBy('id', 'desc')->first();
        if(isset($fire_store_check['firestore_id']) && $fire_store_check['firestore_id']==null)
        {
            $checkAssRawData = Model_ass_rawdata::year($academic_year)->where('ass_main_id', $assmain_id)->orderBy('id', 'desc')->get();
            foreach ($checkAssRawData as $raw_data) {
                $checkAssTrackingData = Model_ass_tracking::year($academic_year)->where('id', $raw_data->id)->orderBy('id', 'desc')->first();
                if ($checkAssTrackingData) {
                    $checkAssScoreData = Model_ass_score::year($academic_year)->where('id', $checkAssTrackingData->score_id)->orderBy('id', 'desc')->first();
                    if ($checkAssScoreData) {
                        $checkAssScoreData->delete();
                    }
                    $checkAssTrackingData->delete();
                }
            }
            $deleteAssRawData = Model_ass_rawdata::year($academic_year)->where('ass_main_id', $assmain_id)->delete();
            $deleteAssMainData = Model_ass_main::year($academic_year)->where('id', $assmain_id)->delete();
        }else{
            $AssessmentServiceLib = new AssessmentServiceLib($school_id);
            $AssessmentServiceLib->deleteMainById($fire_store_check['firestore_id']);
        }
        $pupil_id = $checkAssMain->pupil_id;
        $name = $this->population_model->get($pupil_id);
        $pupil_name = $name->firstname . ' ' . $name->lastname;
        $user = $request->user();
        $user_id = $user->id;
        $del_by_name = $this->population_model->get($user_id);
        $del_by = $del_by_name->firstname . ' ' . $del_by_name->lastname;
        $check_campus = Model_arr_year::year($academic_year)->where('name_id', $pupil_id)->where('field', 'campus')->orderBy('id', 'desc')->first();
        if ($check_campus == NULL) {
            $campus = null;
        } else {
            $campus = $check_campus['value'];
        }
        $data["reason"] = isset($reason) ? $reason : null;
        $data["student_name"] = isset($pupil_name) ? $pupil_name : null;
        $data["deleted_by"] = isset($del_by) ? $del_by : null;
        $data["campus"] = isset($campus) ? $campus : null;
        if ($campus != null) {
            $data["type"] = isset($campus) ? $campus : '';
        } else {
            $data["type"] = 'school';
        }
        $get_lastid = $this->model_del_records->savedel_record($data);
        return response()->json(['message' => 'Assessment deleted']);
    }

    public function fetch_deleted_records($request)
    {
        $school_id = $request->get('school_id');
        $type = $request->get('type') ?? 'school';
        $deleted_records = $this->model_del_records->getDeletedList($type);

        $data = [];

        foreach ($deleted_records as $key => $value) {
            $data[$key]['id'] = $value['id'];
            $data[$key]['reason'] = $value['reason'];
            $data[$key]['name'] = $value['student_name'];
            $data[$key]['deleted_by'] = $value['deleted_by'];
            $data[$key]['deleted_at'] = date('d/m/y', strtotime($value['date_created']));
        }

        return $data;
    }

    public function exportStudentWithAssessmentType($request)
    {
        $school_id = $request->get('school_id');
        $school_code = $request->get('school_code');
        $dat_school = new Model_dat_schools();
        $year = $dat_school->SchoolAcademicYear($school_id);
        $round = RoundLatest($year);
        $academic_year = $year;
        $assessment_round = $request->has('assessment_round') ? $request->get('assessment_round') : $round;
        $type = $request->has('type') ? strtoupper($request->get('type')) : "NOT_STARTED";
        $filter = FullQueryfilter($request, $year);
        dbSchool($school_id);
        $login_url = "https://steer.global/platform/edu/check-step1";
        $data = [];
        if ($type != "NOT_STARTED") {
            $query = Model_ass_main::year($academic_year);
            $query = $query->select('population.firstname', 'population.lastname', 'population.gender', 'population.username', 'population.id', 'population.password', 'population.mis_id', 'population.email_address');
            $query = $query->leftjoin('population', 'population.id', '=', 'ass_main_' . $academic_year . '.pupil_id');
            $filterUpdate = new Filter();
            $query = $filterUpdate->ExportGenericFilter($query, $filter, $academic_year);
            $query = $filterUpdate->ExportArrYearFilter($query, $filter, $academic_year);
            $query = $query->where('population.level', 1);
            $query = $query->groupBy('ass_main_' . $academic_year . '.pupil_id');
            $query = $query->distinct();
            if ($assessment_round) {
                $query = $query->where('ass_main_' . $academic_year . '.round', $assessment_round);
            }
            if ($type == 'COMPLETED') {
                $query = $query->where('is_completed', 'Y');
            }
            if ($type == 'INCOMPLETE') {
                $query = $query->where('is_completed', 'N');
            }
            if ($type == 'MANIPULATED') {
                $query = $query->where('is_manipulated', '1');
            }
            $data = $query->get();
        } else {
            $query = Model_population::select('population.*');
            $filterUpdate = new Filter();
            $query = $filterUpdate->ExportGenericFilter($query, $filter, $academic_year);
            $query = $filterUpdate->ExportArrYearFilter($query, $filter, $academic_year);
            $query = $query->where('population.level', '1');
            $query = $query->groupBy('population.id');
            $query = $query->distinct();
            $query = $query->get();
            foreach ($query as $row) {
                $check = Model_ass_main::year($academic_year)->where('round', $assessment_round)->where('pupil_id', $row->id)->first();
                if (empty($check)) {
                    $add = [
                        'id' => $row->id,
                        'firstname' => $row->firstname,
                        'lastname' => $row->lastname,
                        'gender' => $row->gender,
                        'mis_id' => $row->mis_id,
                        'username' => $row->username,
                        'email_address' => $row->email_address,
                        'password' => $row->password
                    ];
                    $data[] = $add;
                }
            }
        }
        $final = [];
        foreach ($data as $student) {
            $username = strrev($student['username']);
            $pupil_password = $student['password'];
            $password = '';
            if (strlen($pupil_password) >= 30) {
                $password = strrev($this->encdec->dec_password($pupil_password));
            } else {
                $password = strrev($pupil_password);
            }

            $pupil_year_group = $this->arrYear_model->getPupilYear($academic_year, $student['id']);
            $pupil_house = $this->arrYear_model->pupilHouse($academic_year, $student['id']);
            $pupil_form = $this->arrYear_model->getPupilForm($academic_year, $student['id']);
            $pupil_campus = $this->arrYear_model->pupilCampus($academic_year, $student['id']);

            if($pupil_campus){
                $final["students"][] = [
                    'login_url' => $login_url,
                    'mis_id' => $student['mis_id'],
                    'name' => $student['firstname'].' '.$student['lastname'],
                    'gender' => $student['gender'],
                    'email_address' => $student['email_address'] ?? '',
                    'username' => $username,
                    'password' => $password,
                    'year' => !empty($pupil_year_group) ? $pupil_year_group : '',
                    'house' => !empty($pupil_house) ? $pupil_house : '' ,
                    'form' => !empty($pupil_form) ? $pupil_form : '',
                    'campus' => !empty($pupil_campus) ? $pupil_campus : ''
                ];
            }
        }
        return mb_convert_encoding((array)$final, 'UTF-8', 'UTF-8');
    }

    public function get_packages($request)
    {
        $school_id = $request->get('school_id');
        $school_detail = $this->dat_school_model->SchoolDetail($school_id);
        $prduct_arr = $school_detail['products'];
        $ints = array_map('intval', explode(',', $prduct_arr));
        if (in_array(2, $ints)) {
            $permissions['safeguarding'] = TRUE;
        } else {
            $permissions['safeguarding'] = FALSE;
        }
        if (in_array(3, $ints)) {
            $permissions['impact'] = TRUE;
        } else {
            $permissions['impact'] = FALSE;
        }
        return $permissions;
    }
}
