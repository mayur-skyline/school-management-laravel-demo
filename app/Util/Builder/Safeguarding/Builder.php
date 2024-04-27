<?php

namespace App\Util\Builder\Safeguarding;

abstract class Builder 
{
    abstract public function studentdetail(object $user, bool $name_code_schl ): array;

    abstract public function polarRisk(string $label): array;

    abstract public function assessment(array $assessment): array;
}