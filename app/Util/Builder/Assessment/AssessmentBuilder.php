<?php
namespace App\Util\Builder\Assessment;
use App\Services\AstNextuiuxAssessmentProvider;
use App\Models\Dbschools\Model_arr_year;
use App\Libraries\Encdec;
use App\Libraries\AssessmentServiceLib;

class AssessmentBuilder
{
    public function __construct()
    {
        $this->arrYear_model = new Model_arr_year();
        $this->encdec = new Encdec();
    }

    public function round_logic($user_id,$custom_academic_year,$your_school,$sid){
        $rounds_campus = fetchExactData($your_school, $custom_academic_year,"campus");
        $check_exists = false;
        if(isset($rounds_campus->data)){
            $check_campus = Model_arr_year::year($custom_academic_year)->where('name_id', $user_id)->where('field', 'campus')->orderBy('id', 'desc')->first();
            if(isset($check_campus->value) && $check_campus->value=='No Campus'){
                $rounds_school = fetchExactData($your_school, $custom_academic_year,"Main Campus");
                if(isset($rounds_school->data)){
                    $check_exists = true;
                    $round = $rounds_school->data->round;
                }
            }else{
                if(isset($check_campus->value)){
                    $rounds_campus_name = fetchExactData($your_school, $custom_academic_year,"campus",$check_campus->value);
                    if(isset($rounds_campus_name->data)){
                        if (strcasecmp($check_campus->value, $rounds_campus_name->data->name) == 0) {
                            $check_exists = true;
                            $round = $rounds_campus_name->data->round;
                        }
                    }
                }
            }
        }else{
            $rounds_school = fetchExactData($your_school, $custom_academic_year,"Main Campus");
            if(isset($rounds_school->data)){
                $check_exists = true;
                $round = $rounds_school->data->round;
            }
        }
        if($check_exists == false)
            $round = 1;
        return $round;
    }

    function nextassessment_logic($user_id,$custom_academic_year,$your_school,$sid){
        $enc_user_id = $this->encdec->enc_string($user_id);
        $data = array(
            "year" => $custom_academic_year,
            'schoolId' => $your_school,
            "pupilId" => "$enc_user_id",
            "sid" => $sid,
            "is_completed" => true
        );
        $valid_eligible_round = -1;
        try{
            $AssessmentServiceLib = new AssessmentServiceLib($your_school);
            $valid_round = $AssessmentServiceLib->getnextvalidassessment($data);
            if (isset($valid_round['data']) && !empty($valid_round['data'])) {
                $valid_round_exist = $valid_round['data'];
                if (isset($valid_round_exist['round'])) {
                    $valid_eligible_round = $valid_round_exist['round'];
                }
            }
            return $valid_eligible_round;
        } catch (Exception $ex) {
            return -1;
        }
    }
}
