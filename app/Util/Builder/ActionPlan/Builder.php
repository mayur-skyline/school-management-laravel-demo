<?php

namespace App\Util\Builder\ActionPlan;

use Illuminate\Http\Request;

abstract class Builder
{
    abstract public function build(): Array;

    abstract public function buildPupilActionPlan(array $actionPlans, string $type, object $value): Array;

    abstract public function buildCurrentPupilActionPlan(object $data, string $type, object $value, string $lead): Array;

    abstract public function buildActionPlanForSubmission(object $user, Request $request, string $bias, array $sections, string $year_group, string $plantype, string $type): Array;

    abstract public function StudentActionPlanResponses(?string $lead, object $value, string $type, ?string $impact, string $label, bool $name_code_schl): Array;

    abstract public function CollectionActionPlanResponses(object $value, string $type, string $label,$final_review, ?bool $name_code_schl ): Array;

    abstract public function ActionPlanDetails(object $value, array $goals, array $actions, array $description, array $causes, array $risks, array $scores, string $label): Array;

    abstract public function ActionPlanFamilySignPostDetails($value, $goals, $actions, $description, $scores, $label): Array;

    abstract public function CollectionActionPlanDetails(object $value, string $statement, string $type, string $feel, string $title, string $riskType, string $names, string $gender, array $goals, string $label,?array $final_review_array): Array;
}

