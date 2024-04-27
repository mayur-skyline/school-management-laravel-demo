<?php
namespace App\Util\Builder\GroupDash;
use App\Services\AstNextGroupdashboardServiceProvider;
use App\Models\Dbglobal\Model_dat_schools;

class GroupDashBuilder
{
    function final_response_event_tracker($yet_to_take_place,$finished,$date_counter,$implentation_counter,$total_date_percentage,$total_implementation_percentage)
    {
        $final_response['take_place'] = $yet_to_take_place;
        $final_response['take_place']['take_place_count'] = $date_counter;
        $final_response['take_place']['overall_date'] = $total_date_percentage;
        $final_response['finished'] = $finished;
        $final_response['finished']['overall_finished'] = $total_implementation_percentage;
        $final_response['finished']['finished_count'] = $implentation_counter;
        return $final_response;
    }

    function final_response_school_tracker($type,$schooldata,$actionplandata,$stage,$display_school_name,$colour)
    {
        $final_data['current_stage'] = $type;
        $final_data['school_name'] = $display_school_name;
        if(!empty($schooldata))
            $final_data['overview'] = $schooldata;
        $final_data['action_plan_data'] = $actionplandata;
        if($stage=='launch'){
            $counter[0]['id'] = 1;
            $counter[0]['label'] = 'Design Tutorial';
            $counter[1]['id'] = 2;
            $counter[1]['label'] = 'Planning Tutorial';
            $counter[2]['id'] = 3;
            $counter[2]['label'] = 'Assessment 1';
            $counter[3]['id'] = 4;
            $counter[3]['label'] = 'Post Assessment Tutorial 1';
            $counter[4]['id'] = 5;
            $counter[4]['label'] = 'Mid year Review';
            $counter[5]['id'] = 6;
            $counter[5]['label'] = 'Assessment 2';
            $counter[6]['id'] = 7;
            $counter[6]['label'] = 'Post Assessment Tutorial 2';
            $counter[7]['id'] = 8;
            $counter[7]['label'] = 'Assessment 3';
            $counter[8]['id'] = 9;
            $counter[8]['label'] = 'Post Assessment Tutorial 3';
            $counter[9]['id'] = 10;
            $counter[9]['label'] = 'End of year review';
        }else{
            $counter[0]['id'] = 1;
            $counter[0]['label'] = 'Planning Tutorial';
            $counter[1]['id'] = 2;
            $counter[1]['label'] = 'Assessment 1';
            $counter[2]['id'] = 3;
            $counter[2]['label'] = 'Post Assessment Tutorial 1';
            $counter[3]['id'] = 4;
            $counter[3]['label'] = 'Mid year Review';
            $counter[4]['id'] = 5;
            $counter[4]['label'] = 'Assessment 2';
            $counter[5]['id'] = 6;
            $counter[5]['label'] = 'Assessment 3';
            $counter[6]['id'] = 7;
            $counter[6]['label'] = 'End of year review';
        }
        $final_data['slider'] = $counter;
        $final_data['colour_slider'] = $colour;
        return $final_data;
    }

    function final_response_school_tracker_2($type,$assessment_data,$default_stage)
    {
        $final_data['current_stage'] = $type;
        $final_data['default_stage'] = $default_stage;
        $final_data['current_stage_data'] = $assessment_data;
        return $final_data;
    }

    function final_response_skills_page($list,$final_champ,$final_vul)
    {
        $category['list'] = $list;
        $category['champion'] = $final_champ;
        $category['vulnerable'] = $final_vul;
        return $category;
    }

    function final_response_sliderdetails($stage,$count_dt,$count_pt, $count_as1,$count_as2,$count_as3,$count_pat1,$count_pat2,$count_pat3,$count_eyr,$count_myr)
    {
         if($stage=='launch'){
            $counter['slider'][0]['id'] = 1;
            $counter['slider'][0]['label'] = 'Design Tutorial';
            $counter['slider'][0]['school'] = $count_dt;
            $counter['slider'][1]['id'] = 2;
            $counter['slider'][1]['label'] = 'Planning Tutorial';
            $counter['slider'][1]['school'] = $count_pt;
            $counter['slider'][2]['id'] = 3;
            $counter['slider'][2]['label'] = 'Assessment 1';
            $counter['slider'][2]['school'] = $count_as1;
            $counter['slider'][3]['id'] = 4;
            $counter['slider'][3]['label'] = 'Post Assessment Tutorial 1';
            $counter['slider'][3]['school'] = $count_pat1;
            $counter['slider'][4]['id'] = 5;
            $counter['slider'][4]['label'] = 'Mid year Review';
            $counter['slider'][4]['school'] = $count_myr;
            $counter['slider'][5]['id'] = 6;
            $counter['slider'][5]['label'] = 'Assessment 2';
            $counter['slider'][5]['school'] = $count_as2;
            $counter['slider'][6]['id'] = 7;
            $counter['slider'][6]['label'] = 'Post Assessment Tutorial 2';
            $counter['slider'][6]['school'] = $count_pat2;
            $counter['slider'][7]['id'] = 8;
            $counter['slider'][7]['label'] = 'Assessment 3';
            $counter['slider'][7]['school'] = $count_as3;
            $counter['slider'][8]['id'] = 9;
            $counter['slider'][8]['label'] = 'Post Assessment Tutorial 3';
            $counter['slider'][8]['school'] = $count_pat2;
            $counter['slider'][9]['id'] = 10;
            $counter['slider'][9]['label'] = 'End of year review';
            $counter['slider'][9]['school'] = $count_eyr;
        }else{
            $counter['slider'][0]['id'] = 1;
            $counter['slider'][0]['label'] = 'Planning Tutorial';
            $counter['slider'][0]['school'] = $count_pt;
            $counter['slider'][1]['id'] = 2;
            $counter['slider'][1]['label'] = 'Assessment 1';
            $counter['slider'][1]['school'] = $count_as1;
            $counter['slider'][2]['id'] = 3;
            $counter['slider'][2]['label'] = 'Post Assessment Tutorial 1';
            $counter['slider'][2]['school'] = $count_pat1;
            $counter['slider'][3]['id'] = 4;
            $counter['slider'][3]['label'] = 'Mid year Review';
            $counter['slider'][3]['school'] = $count_myr;
            $counter['slider'][4]['id'] = 5;
            $counter['slider'][4]['label'] = 'Assessment 2';
            $counter['slider'][4]['school'] = $count_as2;
            $counter['slider'][5]['id'] = 6;
            $counter['slider'][5]['label'] = 'Assessment 3';
            $counter['slider'][5]['school'] = $count_as3;
            $counter['slider'][6]['id'] = 7;
            $counter['slider'][6]['label'] = 'End of year review';
            $counter['slider'][6]['school'] = $count_eyr;
        }
        foreach($counter['slider'] as $c){
            $numbers[]= $c['school'];
        }
        $low = min($numbers);
        $high = max($numbers);
        $counter['highest_num'] = $high;
        $counter['low_num'] = $low;
        return $counter;
    }

    function gd_sliderdetails($stage)
    {
        if($stage=='launch'){
            $counter[0]['label'] = 'Set Up';
            $counter[0]['value'] = 'stp';
            $counter[1]['label'] = 'Assessment 1';
            $counter[1]['value'] = 'ar1';
            $counter[2]['label'] = 'Action Plan Training 1';
            $counter[2]['value'] = 'apt1';
            $counter[3]['label'] = 'Mid year Review';
            $counter[3]['value'] = 'myr';
            $counter[4]['label'] = 'Assessment 2';
            $counter[4]['value'] = 'ar2';
            $counter[5]['label'] = 'Action Plan Training 2';
            $counter[5]['value'] = 'apt2';
            $counter[6]['label'] = 'Assessment 3';
            $counter[6]['value'] = 'ar3';
            $counter[7]['label'] = 'End of year review';
            $counter[7]['value'] = 'eyr';
        } else {
            $counter[0]['label'] = 'Assessment 1';
            $counter[0]['value'] = 'ar1';
            $counter[1]['label'] = 'Mid year Review';
            $counter[1]['value'] = 'myr';
            $counter[2]['label'] = 'Assessment 2';
            $counter[2]['value'] = 'ar2';
            $counter[3]['label'] = 'Assessment 3';
            $counter[3]['value'] = 'ar3';
            $counter[4]['label'] = 'End of year review';
            $counter[4]['value'] = 'eyr';
        }
        
        return $counter;
    }
    
    public function format_response_ap1($actionplandata): Array
    {
           $dat_school_model = new Model_dat_schools();
           foreach($actionplandata as $k=>$v){
                $finaldata[] = array(
                    'id' => $k,
                    'name'=>$dat_school_model->Custom_SchoolName($k),
                    'action_plans' => array(
                        'student_action_plan' => $actionplandata[$k]['student_action_plan'],
                        'family_signpost' => $actionplandata[$k]['family_signpost'],
                        'monitor_comment' => $actionplandata[$k]['monitor_comment'],
                        'group_action_plan' => $actionplandata[$k]['group_action_plan'],
                        'cohort_action_plan' => $actionplandata[$k]['cohort_action_plan'],
                    ),
                );
            }
            $column = array_column($actionplandata, 'student_action_plan');
            array_multisort($column, SORT_DESC, $finaldata);
            return $finaldata;
    }

    public function format_response_ap2($total_plans_written,$total_action_plans,$total_family_plans,$total_monitor_plans,$total_group_plans,$total_cohort_plans){
        return [
                'total_action_plan' => $total_plans_written,
                'student_action_plan' => $total_action_plans,
                'family_signpost' =>  $total_family_plans,
                'monitor_comment' => $total_monitor_plans,
                'group_action_plan' => $total_group_plans,
                'cohort_action_plan' => $total_cohort_plans,
            ];
    }

    public function format_response_improvement1($actionplanreportdata): Array
    {
        $dat_school_model = new Model_dat_schools();
        foreach($actionplanreportdata as  $k=>$v){
            $finaldata[] = array(
                'id' => $k,
                'name'=>$dat_school_model->Custom_SchoolName($k),
                'improvement' => $actionplanreportdata[$k]['improvement_percent'] == 0 ? null : $actionplanreportdata[$k]['improvement_percent'],
                'review' => $actionplanreportdata[$k]['review_percent'] == 0 ? null : $actionplanreportdata[$k]['review_percent'],
            );
        }
        $column = array_column($actionplanreportdata, 'improvement_percent');
        array_multisort($column, SORT_DESC, $finaldata);
        return $finaldata;
    }

    public function format_overall_improvement($actionplanreportdata,$total_action_plans_allschools){
        $cal = $final_calc = 0;
        foreach($actionplanreportdata as $v){
            $improvement_each_school = $v['improvement_percent'];
            $student_action_plan_each_school = $v['report_student_aps'];
            $cal+= ($improvement_each_school/100)*$student_action_plan_each_school;
        }
        if( $total_action_plans_allschools == 0 )
            $total_action_plans_allschools = 1;
        $final_calc = round(100*($cal/$total_action_plans_allschools),2);
        $final_calc = $cal == 0 ? null : $final_calc;
        return $final_calc;
    }

    public function format_overall_review($actionplanreportdata,$total_action_plans_review_allschools){
        $cal = $final_calc = 0;
        foreach($actionplanreportdata as $k=>$v){
            $review_each_school = $actionplanreportdata[$k]['review_percent'];
            $student_action_plan_each_school = $actionplanreportdata[$k]['totalstudent_aps_review'];
            $cal+= $review_each_school/100*$student_action_plan_each_school ;
        }
        if( $total_action_plans_review_allschools == 0 )
            $total_action_plans_review_allschools = 1;
        $final_calc = round(100*($cal/$total_action_plans_review_allschools),2);
        $final_calc = $cal == 0 ? null : $final_calc;
        return $final_calc;
    }
    function findPhaseValue($search_value, $array_value)
    {
        $flat_values = array_column($array_value, 'value');
        $flat_label = array_column($array_value, 'label');
        $phase_value_key = array_search($search_value, $flat_values);
        if ($phase_value_key !== false) {
            $responce['phase_label'] = $flat_label[$phase_value_key];
            $responce['phase_value'] = $flat_values[$phase_value_key];
            return $responce;
        }

        return false;
    }
}
