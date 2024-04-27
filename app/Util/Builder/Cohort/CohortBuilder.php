<?php

namespace App\Util\Builder\Cohort;
use App\Util\Builder\Cohort\Builder;
use App\Util\Grouping\PolarRisk\PolarRisk;
use Illuminate\Support\Facades\Log;
use DateTime;

class CohortBuilder extends Builder {
    public function __construct()
    {

    }

    public function buildResponses(string $formated_date, string $score, string $biasname, string $school_info_type, ?string $polar_risk_name): Array
    {
        $bias = $this->Params($biasname, $score);
        $data = array(
                "label" => $school_info_type,
                "type"  => $school_info_type,
                "factor_biases" => array(
                    array(
                        "label" =>  $biasname,
                        "type"  =>  $bias,
                        "value" =>  (float)$score,
                        "trends" => array(
                            array(
                                "date" => $formated_date,
                                "value" => (float)$score,
                            )
                        ),
                        "description" => Description($score, $bias)
                    )
                )
        );

        if($polar_risk_name != null) {
            $data["factor_biases"]["polar_bias"] = $this->PolarRiskObject($polar_risk_name, $formated_date);
        }
        return $data;
    }

    public function PolarRiskObject($polar_risk, $date)
    {
        return array(
                "type" => strtoupper( str_replace(' ', '_', trim( $polar_risk ) ) ),
                "label" => $polar_risk,
                "date" => $date
            );
    }

    public function buildFactorBias(string $formated_date, string $score, string $biasname, ?string $polar_risk_name): Array
    {
        $bias = $this->Params($biasname, $score);
        $data =  array(
                "label" =>  $biasname,
                "type"  =>  $bias,
                "value" =>  (float)$score,
                "trends" => array(
                    array(
                        "date" => $formated_date,
                        "value" => (float)$score
                    )
                ),
                "description" => Description($score, $bias),
        );
        if($polar_risk_name != null) {
            $data["polar_bias"] = $this->PolarRiskObject($polar_risk_name, $formated_date);
        }
        return $data;
    }

    public function trend(float $value, string $date): Array
    {
        return [
            "date" => $date,
            "value" => (float)$value
        ];
    }
    public function Params(string $biasname) {
        $bias = strtoupper( str_replace(' ', '_', $biasname) );
        return $bias;
    }

    public function action_plan( object $actionPlan, string $assessment_type ): Array
    {
        return [
            'id' => $actionPlan->id,
            'type' => $assessment_type == "IN_SCHOOL" ? "STUDENT_ACTION_PLAN" : "FAMILY_SIGNPOST",
            'description' => $actionPlan->risk['label'],
            'date_created' => $actionPlan->created_at
        ];
    }

    public function grp_action_plan( object $actionPlan, string $type ): Array
    {
        return [
            'id' => $actionPlan->id,
            'type' => $type,
            'description' => $actionPlan->risk['label'],
            'date_created' => $actionPlan->created_at
        ];
    }

    public function createFactorBias(string $label, object $value ): Array
    {
        $score = ScoreBasedOnLabel( $label, $value );
        $date = new DateTime($value->datetime);
        $date = $date->format('d.m.Y') ?? null;
        //logic to replace eg:Trust Of Others to Trust of Others ---start
        $first = ucwords(strtolower(str_replace( '_', ' ', $label)));
        $pattern = "/ Of /";
        $replace = " of ";
        $custom_label = preg_replace($pattern,$replace,$first);
       //logic to replace eg:Trust Of Others to Trust of Others ---end
        $data =  array(
                "label" =>  $custom_label,
                "type"  =>  $label,
                "value" =>  $score,
                "trends" => array(
                    array(
                        "date" => $date,
                        "value" => $score,
                    )
                ),
                "description" => Description($score, $label),
        );
        $risk = RiskName( $score, $label );
        if( $risk != null)
            $data["polar_bias"] = $this->PolarRiskObject($risk,  $date );
        return $data;
    }

    public function objectCohortBuilder( string $label, array $factor_biases, array $composite_biases, array $action_plans, array $current_action_plans ): Array
    {
        return [
            "label" => $label,
            "type" =>  $label,
            "factor_biases" => $factor_biases,
            "composite_biases" => $composite_biases,
            "action_plans" => $action_plans,
            "current_action_plans" => $current_action_plans
        ];
    }

    public function pupilCohortMeta( $has_priority, $priority_count, $completed_status, $is_manipulated, $speed, $change_in_bias ): array
    {
        return [
            "completion_status" => $completed_status,
            "tracking_speed" => $speed,
            "is_manipulated" => $is_manipulated,
            "is_priority" => $has_priority,
            "priority_score" => $priority_count,
            "change_in_bias" => $change_in_bias
        ];
    }

    public function combinePupilCohortdata( $variants, $meta, $pupil_data )
    {
        return [
            "student" => $pupil_data,
            "assessment_information" => $meta,
            "variants" => $variants,
        ];
    }

}
