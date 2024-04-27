<?php

namespace App\Util\Builder\Cohort;

abstract class Builder {
    abstract public function buildResponses(string $formated_date, string $score, string $biasname, string $school_info_type, ?string $polar_risk_name): Array;

    abstract public function buildFactorBias(string $formated_date, string $score, string $biasname, ?string $polar_risk_name): Array;

    abstract public function trend(float $value, string $date): Array;

    abstract public function action_plan( object $actionPlan, string $assessment_type ): Array;

    abstract public function objectCohortBuilder( string $label, array $factor_biases, array $composite_biases, array $action_plans, array $current_action_plans ): Array;
}
