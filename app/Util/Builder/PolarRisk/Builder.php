<?php

namespace App\Util\Builder\PolarRisk;

use Illuminate\Http\Request;

abstract class Builder
{
    abstract public function buildPolarObject( object $assessments ): Array;

}

