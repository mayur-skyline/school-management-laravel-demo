<?php

namespace App\Util\Builder\PolarRisk;

use App\Util\Builder\PolarRisk\Builder;
use DateTime;

class PolarBuilder extends Builder {

    public function __construct()
    {

    }
    public function buildPolarObject( object $assessments ): Array
    {
        $polar_bias = [];
        foreach( $assessments as $assessment ) {
            $polar_bias[$assessment->student_id] = [];
            if( $assessment->P <= 3 ) 
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->P, "label" => 'Polar Low Self Disclosure', 'type' => 'POLAR_LOW_SELF_DISCLOSURE' ];
            if( $assessment->P >= 12 )
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->P, "label" => 'Polar High Self Disclosure', 'type' => 'POLAR_HIGH_SELF_DISCLOSURE' ];
            if( $assessment->S <= 3 ) 
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->S, "label" => 'Polar Low Trust Of Self', 'type' => 'POLAR_LOW_TRUST_OF_SELF' ];
            if( $assessment->S >= 12 )
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->S, "label" => 'Polar High Trust Of Self', 'type' => 'POLAR_HIGH_TRUST_OF_SELF' ];
            if( $assessment->L <= 3 ) 
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->L, "label" => 'Polar Low Trust Of Others', 'type' => 'POLAR_LOW_TRUST_OF_OTHERS' ];
            if( $assessment->L >= 12 )
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->L, "label" => 'Polar High Trust Of Others', 'type' => 'POLAR_HIGH_TRUST_OF_OTHERS' ];
            if( $assessment->X <= 3 ) 
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->X, "label" => 'Polar Low Seeking Change', 'type' => 'POLAR_LOW_SEEKING_CHANGE' ];
            if( $assessment->X >= 12 )
                $polar_bias[$assessment->student_id][] =  [ "value" => (float)$assessment->X, "label" => 'Polar High Seeking Change', 'type' => 'POLAR_HIGH_SEEKING_CHANGE' ];
        }

        return $polar_bias;

    }

    public function studentTrends( $assessment, $polar_biases, $bias, $name_code_schl ) {
        $name_code = $name_code_schl == true ? $assessment->firstname . " " . $assessment->lastname  : getUserNameCode( $assessment );
        return [
            "id" => $assessment->student_id,
            "name" => $assessment->firstname . " " . $assessment->lastname,
            "gender" => $assessment->gender == "m" ? "MALE" : "FEMALE",
            "factor_score" => number_format($assessment->{$bias}, 1),
            "polar_biases" => $polar_biases[ $assessment->student_id ],
            "is_historic" => $assessment->pupil_id_in_next_round ? true : false,
            'name_code' => $name_code
        ];
        
    }
}