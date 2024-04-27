<?php

namespace App\Util\Builder\Report;

use Illuminate\Http\Request;
use App\Models\Dbschools\Model_population;
use App\Util\Grouping\AssessmentImprovement\AssessmentImprovement;
use App\Util\Grouping\PolarRisk\PolarRisk;
use App\Util\Grouping\Composite\Composite;

abstract class ReportBuilder
{
    protected $population = null;
    protected $assImprovement = null;
    protected $polar_risk = null;
    protected $composite_bias = null;
    public function __construct()
    {
        $this->population = new Model_population();
        $this->assImprovement = new AssessmentImprovement();
        $this->polar_risk = new PolarRisk();
        $this->composite_bias = new Composite();
    }

    abstract public function buildActionPlanReport(string $date,int $total_students, array $student_action_plan_summary, array $family_signpost_plan_summary,
                                                   array $cohort_action_plan_summary, array $group_action_plan_summary,
                                                   array $monitor_comment_summary): Array;

    abstract public function buildCohortReport(array $priority_students, array $composite_biases, array $polar_biases, array $meta_responses): Array;

    abstract public function action_plan_summary( object $data, array $current_assessment_list, array $immediate_past_assessment_list, ?float $previous_score, ?float $current_score ): Array;

    abstract public function action_plan_summary_object( int $assessment_improvement, array $teacher_review, array $action_plan_summary ): Array;

    abstract public function groupaction_plan_summary(object $data, array $current_assessment_list, array $immediate_past_assessment_list, ?float $current_score, ?float $previous_score,$studentdetails,?string $review): Array;

    abstract public function cohortaction_plan_summary(string $displayword,object $data,int $current_percent,int $previous_percent,float $current_mean_percent,float $prev_mean_percent,float $red_in_bias): Array;

    abstract public function student_details(object $studentdetail): Array;

    abstract public function priority_students(): Array;

    abstract public function assessment_type ( array $composite_bias_students, string $assessment_type, float $percentage ): array;

    abstract public function assessment_type_builder( float $percentage, array $composite_bias_students ): Array;

    abstract public function polar_assessment_type( float $percentage, string $assessment_type ): Array;

    abstract public function polar_assessment_type_builder( float $percentage  ): Array;

    abstract  public function report_summary( object $data, array $composite_biases ): Array;

}

