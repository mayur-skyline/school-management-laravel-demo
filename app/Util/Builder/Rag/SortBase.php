<?php

namespace App\Util\DBFilter;

use Illuminate\Database\Eloquent\Builder;

abstract class SortBase {

    abstract public function SortByFactorBias(Builder $query, array $sort_param ): Builder;

}