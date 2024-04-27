<?php

namespace App\Util\Builder\Composite;

use App\Services\ActionPlanServiceProvider;
use App\Util\Builder\Composite\Builder;
use DateTime;

class CompositeBuilder extends Builder {

    public function build(): Array
    {
        return [];
    }

    public function pupilcohortcomposite( $label, $date ): Array
    {
        return [
            "label" =>  $label,
            "type" => strtoupper( str_replace(' ','_', $label)  ),
            "date"  =>  isset($date['formated_date']) ? $date['formated_date'] : $date
        ];
    }
}
