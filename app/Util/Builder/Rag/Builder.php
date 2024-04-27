<?php

namespace App\Util\Builder\Rag;

use Illuminate\Http\Request;

abstract class Builder
{
    abstract public function studentObject( object $value, array $builder, array $userList, bool $name_code_schl ): Array;

    abstract public function assessmentScore( array $builder, integer $pos, string $assessment_type, object $value ): Array;

    abstract public function ObjectBuilder( array $data, array $builder, array $userList, string $assessment_type, array $priorityList ): Array;
}

