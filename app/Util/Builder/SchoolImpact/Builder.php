<?php

namespace App\Util\Builder\SchoolImpact;


abstract class Builder
{
    public function __construct()
    {
        
    }

    abstract public function assessmentTypeOverview( $current_polar_bias_percentage );

    abstract public function assessmentTypePastAssessment( array $data, string $assessment_type, $past_assessments );

    abstract public function assessmentTypeGender( array $data, string $assessment_type, $gender );

    abstract public function assessmentTypeYearGroup( string $assessment_type, $year_groups );

    abstract public function assessmentTypeHouse( string $assessment_type, $houses );

    abstract public function polar_biases( array $polar_biases ): Array;

    abstract public function polar_bias_by_factor( array $polar_biases_by_factor ): Array;

    abstract  public function priority_summary( array $priority_summary ): Array;
}