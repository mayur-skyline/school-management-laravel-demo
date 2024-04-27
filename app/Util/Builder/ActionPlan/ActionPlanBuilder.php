<?php

namespace App\Util\Builder\ActionPlan;

use App\Services\ActionPlanServiceProvider;
use App\Util\Builder\ActionPlan\Builder;
use DateTime;
use Illuminate\Support\Facades\Auth;
use App\Util\Grouping\User\User;

class ActionPlanBuilder extends Builder {

    public function __construct()
    {
        $this->user = new User();
    }
    public function build(): Array
    {
        return [];
    }

    public function buildCurrentPupilActionPlan($actionPlan, $type, $value, $lead ): Array
    {
        return array(
            'id' => $actionPlan->id,
            'student' => array(
                'id' => $value->id,
                'name' => $value->firstname.' '.$value->lastname,
                'gender' => $value->gender,
                'name_code' => NameCode( $value->id )
            ),
            'type' => $type,
            'label' => 'Student Action Plan',
            'due' => strtotime(date('Y-m-d')) > strtotime($actionPlan->review_date) ? true : false,
            'risk' => array(
                'label' => BiasAbreviationToName($actionPlan->bias),
                'type'  => biasType( BiasAbreviationToName($actionPlan->bias) )
            ),
            'lead' => $lead,
            'review_date' => $actionPlan->review_date,
            'created_at' => $actionPlan->date_created
        );

    }


    public function buildPupilActionPlan($actionPlans, $type, $value): Array
    {
        $data = [];
        $actionPlanService = new ActionPlanServiceProvider();
        $label = $type == 'STUDENT_ACTION_PLAN' ? 'Student Action Plan' : 'Family Signpost';
        $name_code_schl = IsSchoolNameCode();
        foreach ($actionPlans as $plankey => $actionPlan) {
            $lead = $actionPlanService->ResponsibilityOn($actionPlan->statements);
            $data[] =  array(
                'id' => $actionPlan->id,
                'student' => array(
                    'id' => $value->id,
                    'name' => $value->firstname.' '.$value->lastname,
                    'gender' => $value->gender,
                    'name_code' => $name_code_schl == true ? ( $value->firstname.' '.$value->lastname ) : getUserNameCode( $value )
                ),
                'type' => $type,
                'label' => $label,
                'due' => strtotime(date('Y-m-d')) > strtotime($actionPlan->review_date) ? true : false,
                'risk' => array(
                    'label' => BiasAbreviationToName($actionPlan->bias),
                    'type'  => biasType( BiasAbreviationToName($actionPlan->bias) )
                ),
                'lead' => $lead,
                'review_date' => $actionPlan->review_date,
                'created_at' => $actionPlan->date_created
            );
        }
        return $data;
    }

    public function buildPupilMonitorComment($actionPlans, $type, $value): Array
    {
        $data = [];
        $actionPlanService = new ActionPlanServiceProvider();
        $label = 'Monitor Comment';
        $name_code_schl = IsSchoolNameCode();
        foreach ($actionPlans as $plankey => $actionPlan) {
            $risklabel = str_replace('_', ' ', $actionPlan->bias);
            $rlabel = ucwords( strtolower( $risklabel ) );
            //$lead = $actionPlanService->ResponsibilityOn($actionPlan->statements);
            $data[] =  array(
                'id' => $actionPlan->id,
                'student' => array(
                    'id' => $value->id,
                    'name' => $value->firstname.' '.$value->lastname,
                    'gender' => $value->gender,
                    'name_code' => $name_code_schl == true ? ( $value->firstname.' '.$value->lastname ) : getUserNameCode( $value )
                ),
                'type' => $type,
                'label' => $label, 
                //'due' => strtotime(date('Y-m-d')) > strtotime($actionPlan->review_date) ? true : false,
                'risk' => array(
                    'label' => $rlabel,
                    'type'  => $actionPlan->bias,
                ),
                'lead' => null,
                'review_date' => null,
                'created_at' => $actionPlan->created
            );
        }
        return $data;
    }

    public function buildActionPlanForSubmission($user, $request, $bias, $sections, $year_group, $plantype, $type): Array
    {
        $data['created_by'] = $user->id;
        $data['created_on'] = $request->student_id;
        $data['bias'] = $bias;
        $data['plan_type'] = $plantype;
        $data['statements'] = $sections;
        $data['title'] = "Action_Plan_written_on_".date('Y-m-d')."_by_".$user->firstname."";
        $data["year_group"] = $year_group;
        $data["review_date"] = new DateTime($request->review_date);
        $data["authors"] = $user->firstname.' '.$user->lastname;
        $data['date_created'] = new DateTime();
        if($type == 'STUDENT_ACTION_PLAN') {
            $data['is_saved'] = '0';
            $data["filter"] = "";
            $data["review_reminder"] = '1';
        }
        if($type == 'FAMILY_SIGNPOST') {
            $data['fp_comment'] = "";
            $data['version'] = 2;
        }
        return $data;
    }

    public function StudentActionPlanResponses(?string $lead, object $value, string $type, ?string $impact, string $label, bool $name_code_schl ): Array
    {
        return array(
                'id' => $value->id,
                'student' => array(
                    'id' => $value->student_id,
                    'name' => $value->firstname.' '.$value->lastname,
                    'gender' => $value->gender,
                    'name_code' =>  $name_code_schl == true ? ( $value->firstname.' '.$value->lastname ) : getUserNameCode( $value )
                ),
                'type' => $type,
                'label' => $label,
                'impact' => $impact,
                'review' => $value->review,
                'actual_review_date' => $value->actual_review_date,
                'reviewer_id' => $value->reviewer_id,
                'due' => strtotime(date('Y-m-d')) > strtotime($value->review_date) ? true : false,
                'risk' => array(
                    'label' => BiasAbreviationToName($value->bias),
                    'type'  => biasType( BiasAbreviationToName($value->bias) )
                ),
                'lead' => $lead,
                'review_date' => $value->review_date,
                'created_at' => $value->date_created
        );
    }

    public function CollectionActionPlanResponses(object $value, string $type, string $label, $final_review, ?bool $name_code_schl ): Array
    {
        $listing =  array(
            'id' => $value->id,
            'student' => array(
                'id' => isset( $value->names ) ? $value->pop_id : 'Group of Student Ids',
                'name' => isset( $value->names ) ? $value->names : 'Group of Student',
                'name_code' => ( isset($name_code_schl) && $name_code_schl == true ) ? $value->names : NameCode_group( $value->pop_id )
            ),
            'type' => $type,
            'label' => $label,
            "due" =>  strtotime(date('Y-m-d')) > strtotime($value->review_date) ? true : false,
            'risk' => array(
                'label' => BiasAbreviationToName($value->type_banc),
                'type'  => biasType( BiasAbreviationToName($value->type_banc) ),
                'risk_type' => isset( $value->riskType ) ? $value->riskType : null,
            ),
            'impact' => isset( $value->impact ) ? $value->impact : null,
            'lead' => $value->authors,
            'review_date' => $value->review_date,
            'created_at' => isset( $value->date_time ) ? $value->date_time : null,
            'filters_title' => isset( $value->title_filter ) ? $value->title_filter : null,
            'review' => isset( $final_review ) ? $final_review : null
        );
        if( isset( $value->title_filter ) && $value->title_filter == ''){
            unset($listing['filters_title']);
        }
        return $listing;
    }

    public function ActionPlanDetails($value, $goals, $actions, $description, $causes, $risks, $scores, $label): Array
    {
        return array(
            'id' => $value->id,
            'student' => $value->student,
            'type' => $value->type,
            'label' => $label,
            'due' => $value->due,
            'lead' => $value->lead,
            'impact' => $value->impact,
            'review' => $value->review,
            'reviewed_at' => $value->actual_review_date,
            'reviewer'  => $this->user->User( $value->reviewer_id ),
            'review_date' => $value->review_date,
            'created_at' => $value->created_at,
            'goals' => $this->replaceName( $goals, $value, 'goal' ),
            'school_action' => $actions,
            'risk' => $description,
            'reasons' => $this->replaceNameReason( $causes, $value, 'reasons' ),
            'future_risks' => $this->replaceNameRisk( $risks, $value ),
            'scores' => $scores
        );
    }

    public function ActionPlanFamilySignPostDetails($value, $goals, $actions, $description, $scores, $label): Array
    {
        return array(
            'id' => $value->id,
            'student' => $value->student,
            'type' => $value->type,
            'label' => $label,
            'due' => $value->due,
            'lead' => $value->lead,
            'impact' => $value->impact,
            'review' => $value->review,
            'reviewed_at' => $value->actual_review_date,
            'reviewer'  => $this->user->User( $value->reviewer_id ),
            'review_date' => $value->review_date,
            'created_at' => $value->created_at,
            'goals' => $goals,
            'school_action' => $actions,
            'risk' => $description,
            'scores' => $scores
        );
    }

    public function CollectionActionPlanDetails($value, $statement, $type, $feel, $title, $riskType, $names, $gender, $goals, $label,$final_review_array): Array
    {
        $detailed = array(
            'id' => $value->id,
            'student' => array(
                'id' => $value->pop_id,
                'name' => $names,
                'gender' => $gender,
                'name_code' => isset($value->pop_id)?NameCode_group( $value->pop_id ):null
            ),
            'type' => $type,
            'label' => $label,
            'risk' => array(
                'label' => BiasAbreviationToName($value->type_banc),
                'type'  => biasType( BiasAbreviationToName($value->type_banc) ),
                'feel'  => trim($feel),
                'statement'  => trim($statement),
                'risk_type' => $riskType
            ),
            'lead' => $value->authors,
            'review' => (isset($final_review_array) && !empty($final_review_array))?$final_review_array:$value->review,
            'reviewed_at' => $value->actual_review_date,
            'reviewer'  => $this->user->User( $value->reviewer_id ),
            'review_date' => $value->review_date,
            'created_at' => date('Y-m-d', strtotime($value->date_created)),
            'goals' => $goals,
            'school_action' =>  trim($value->comment),
            'filters_title' =>  isset($title) ? $title : '',
        );
        if($title == ''){
            unset($detailed['filters_title']);
        }
        return $detailed;
    }

    public function replaceName( $data, $value, $type ) {
        $name_code_schl = IsSchoolNameCode();
        if ( $name_code_schl )
            return $data;
        
        if( auth()->user()->level < 6 && $this->pseudomiseStatus() == false )
            return $data;
        
        foreach( $data as $key => $d ) {
            $data[$key][ $type ] = str_replace( $value->student['name'], $value->student['name_code'], $d[$type] );
            foreach( $d['signposts'] as $key2 => $sp ) {
                $data[$key][ 'signposts' ][$key2]['signpost'] = str_replace( $value->student['name'], $value->student['name_code'], $sp['signpost'] );
            }
        }
        return $data;
    }
    
    
    public function replaceNameReason ( $data, $value, $type ) {
        $name_code_schl = IsSchoolNameCode();
        if ( $name_code_schl )
            return $data;

        if( auth()->user()->level < 6 && $this->pseudomiseStatus() == false )
            return $data;

        $data['title'] = str_replace( $value->student['name'], $value->student['name_code'], $data['title'] );
        foreach( $data[$type] as $key => $d ) {
            $data[ $type ][$key] = str_replace( $value->student['name'], $value->student['name_code'], $d );
        }
        return $data;
    }
    
    public function replaceNameRisk( $risks, $value ) {
        $name_code_schl = IsSchoolNameCode();
        if ( $name_code_schl )
            return $risks;
       
        if( auth()->user()->level < 6 && $this->pseudomiseStatus() == false )
            return $risks;

        foreach( $risks as $key => $risk ) {
            $risks[$key]['title'] = str_replace( $value->student['name'], $value->student['name_code'], $risk['title'] );
            foreach( $risk['risks'] as $key2 => $r )
                $risks[$key]['risks'][$key2] = str_replace( $value->student['name'], $value->student['name_code'], $r );
        }

        return $risks;
    }

    public function pseudomiseStatus() {
        $switch_response = fetchSwitchData( request()->school_id );
        if ($switch_response->status == true) {
            if (isset($switch_response->data->value)) {
                if ($switch_response->data->value == false) {
                    return false;
                } 
                return true;
            }
        }
        return false;
    }
}
