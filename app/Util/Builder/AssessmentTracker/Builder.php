<?php

namespace App\Util\Builder\AssessmentTracker;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

abstract class Builder
{
    abstract public function studentdetail(object $student, bool $name_code_schl, array $user_code, Collection $student_properties ): Array;

    abstract public function questionAnswerAggregate(object $rawdata, array $questions, array $answers, string $type): Array;

    abstract public function otherparam(object $record, string $type): Array;

    abstract public function responses(array $student_data, array $assessmentTime, array $structure, object $rawdata, ?string $keyword): Array;

}

