<?php

namespace App\Util\Builder\Report;
use App\Util\Builder\Report\ReportBuilder;

class Report extends ReportBuilder
{
    public function compValue( $score, $bias ) {
        if( $score == 7.5 )
            return 'Not Present';
        else
            return 'Present';
    }
    public function buildActionPlanReport(string $date,int  $total_students,array $student_action_plan_summary, array $family_signpost_plan_summary,
                                                   ?array $cohort_action_plan_summary, ?array $group_action_plan_summary,
                                                   ?array $monitor_comment_summary): Array
    {
        return [
            'generated_date' => $date,
            'no_of_students' => $total_students,
            'student_action_plan_summary' => $student_action_plan_summary,
            'family_signpost_plan_summary' => $family_signpost_plan_summary,
            'cohort_action_plan_summary' => $cohort_action_plan_summary,
            'group_action_plan_summary' => $group_action_plan_summary,
            'monitor_comment_summary' => $monitor_comment_summary
        ];
    }

    public function action_plan_summary( object $data, array $current_assessment_list, array $immediate_past_assessment_list, ?float $current_score, ?float $previous_score ): Array
    {
        return [
                "student" =>  $this->student_details($data),
                "focus" =>  trim( str_replace( 'Polar', '' , BiasAbreviationToName($data->bias) ) ),
                "previous_score" => !isCompositeBias( $data->bias ) ? $previous_score : 'Present',
                "current_score" => !isCompositeBias( $data->bias ) ? $current_score : $this->CompValue($current_score, $data->bias),
                "assessment_improvement" => $this->assImprovement->StudentAssessmentImprovement( $current_score, $previous_score ),
                "teacher_review" =>  Review( $data->review )
        ];
    }

    public function action_plan_summary_object( int $assessment_improvement, array $teacher_review, array $action_plan_summary ): Array
    {
        return [
            "assessment_improvement" => $assessment_improvement,
            "teacher_review" =>  $teacher_review,
            "action_plan_summary" => $action_plan_summary
        ];
    }

    public function groupaction_plan_summary( object $data, array $current_assessment_list, array $immediate_past_assessment_list, ?float $current_score, ?float $previous_score,$studentdetails,$review ): Array
    {
        return [
                'id' => $data->id,
                'student' => $this->student_details($studentdetails),
                "focus" => trim( str_replace( 'Polar', '' , BiasAbreviationToName($data->type_banc))),
                "previous_score" => !isCompositeBias( $data->type_banc ) ? $previous_score : 'Present',
                "current_score" =>  !isCompositeBias( $data->type_banc ) ? $current_score : $this->CompValue($current_score, $data->bias),
                "assessment_improvement" => $this->assImprovement->StudentAssessmentImprovement( $current_score, $previous_score ),
                 "teacher_review" => isset($review)?$review:null
        ];
    }

    public function cohortaction_plan_summary($displayword,$data,$current_percent,$previous_percent,$current_mean_percent,$prev_mean_percent,$red_in_bias): Array
    {
        $reduction_in_bias = 0;
        $improvement = false;
        if(isset($data->review) && $data->review=='POSITIVE_IMPACT')
            $data->review = true;
        elseif(isset($data->review) && $data->review=='NO_IMPACT_YET')
            $data->review = false;
        if($current_percent<$previous_percent)
            $improvement = true;
        else
            $improvement = false;
        
        if( $previous_percent - $current_percent > 0 ) 
            $reduction_in_bias = round(( ( $previous_percent - $current_percent ) / $previous_percent ) *100, 2);
        
        return [
                'id' => $data->id,
                "title" => $displayword,
                "focus" => trim( str_replace( 'Polar', '' , BiasAbreviationToName($data->type_banc))),
                "current_assessment" => $current_percent,
                "previous_assessment" => $previous_percent,
                "previous_mean" => $prev_mean_percent,
                "current_mean" => $current_mean_percent,
                "assessment_improvement" => $improvement,
                "reduction_in_bias"=> $reduction_in_bias, //$red_in_bias,
                "teacher_review" =>  Review( $data->review )
        ];
    }

    public function student_details($studentdetail): Array
    {
            return [
                "id" => $studentdetail->student_id,
                "name" => $studentdetail->firstname.' '.$studentdetail->lastname,
                "gender" => $studentdetail->gender,
                'name_code' => NameCode( $studentdetail->student_id, $studentdetail ),
            ];
    }

    public function buildCohortReport(array $priority_students, array $composite_biases, array $polar_biases, array $meta_responses): Array
    {
        return [
            'no_of_students' => isset($meta_responses['no_of_students'])?$meta_responses['no_of_students']:0,
            'generated_date' => $meta_responses['generated'],
            'priority_students' => !empty($priority_students)?$priority_students:[],
            'composite_biases' => !empty($composite_biases)?$composite_biases:[],
            'polar_biases' => !empty($polar_biases)?$polar_biases:[]
        ];
    }

    public function cohortReportObject( int $assessment_improvement, int $teacher_review, array $action_plan_summary ): Array
    {
        return [
            "composite_bias" => $assessment_improvement,
            "composite_bias_students" =>  $teacher_review
        ];
    }

    public function report_summary( object $data, array $composite_biases ): Array
    {
        return [
                "student" =>  $this->student_details($data),
                "risks" =>  $this->CompositeRiskcount( $data, $composite_biases  ),
                "flag" => 1,//$this->polar_risk->StudentPolarRisks( $data ) > 1 ? 1 : 0,
                "composite_bias" => $this->composite_bias->ExtractOnlyCompositeBias( $composite_biases )
        ];
    }

    public function assessment_type( array $composite_bias_students, string $assessment_type, float $percentage ): Array
    {
        return [
                $assessment_type =>  $this->assessment_type_builder( $percentage, $composite_bias_students )
        ];
    }

    public function polar_assessment_type( float $percentage, string $assessment_type ): Array
    {
        return [
                $assessment_type =>  $this->polar_assessment_type_builder( $percentage  )
        ];
    }

    public function polar_assessment_type_builder( float $percentage  ): Array
    {
        return [
                "polar_bias_percentage" => round( $percentage, 2 )
        ];
    }

    public function assessment_type_builder( float $percentage, array $composite_bias_students ): Array
    {
        return [
                "composite_bias_percentage" => round( $percentage, 2 ),
                "composite_bias_students" => $composite_bias_students
        ];
    }

    public function CompositeBias( array $composite_biases ): Array
    {
        return [

        ];
    }

    public function priority_students(): Array
    {
        return [
            'IN_SCHOOL'=>[
                'priority_percentage' => 0,
                'priority_students' => [],
            ],
              'OUT_OF_SCHOOL'=> [
                'priority_percentage'=> 0,
                'priority_students'=> [],
              ]
        ];
    }

    public function CompositeRiskcount( $data, $composite_biases  ): int
    {
        //$polar_risks = $this->polar_risk->StudentPolarRisks( $data );
        $composite_risks = count( $composite_biases );
        return $composite_risks;

    }
}
