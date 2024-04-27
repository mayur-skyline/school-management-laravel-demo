<?php

namespace App\Util\Builder\Composite;

use Illuminate\Http\Request;

abstract class Builder
{
    abstract public function build(): Array;

    abstract public function pupilcohortcomposite(string $label, array $score): Array;
}

