<?php

namespace App\Util\Builder\Safeguarding;

class SafeguardingBuilder 
{
    public function studentdetail(object $data, bool $name_code_schl ): array
    {
        return [
            'id' => $data->student_id,
            'name' => $data->firstname.' '.$data->lastname,
            'gender' => $data->gender,
            'name_code' => $name_code_schl == true ? $data->firstname : getUserNameCode( $data )
        ];
    }

    public function risk(string $label): array
    {
        return [
            'label' => $label,
            'type' => strtoupper( str_replace(' ','_', $label ) ),
            'description' => safeguardingDescription($label)
        ];
    }

    public function description(string $label): array 
    {
        return [
            'label' => $label
        ];
    }

    public function assessment(array $assessment): array
    {
        list('risks' => $risks, 'risk_count' => $risk_count, 'year' => $year, 'date' => $date) = $assessment;
        return [
            'risk_count' => $risk_count,
            'year' => $year,
            'date' => $date,
            'risks' => $risks
        ];
    }
}