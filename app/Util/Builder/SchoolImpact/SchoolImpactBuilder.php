<?php

namespace App\Util\Builder\SchoolImpact;

class SchoolImpactBuilder
{
    public function __construct()
    {

    }

    public function priority_summary( $priority_summary ): Array
    {
        return [
            "priority_students" => $priority_summary
        ];
    }

    public function composite_bias( $composite_biases ): Array
    {
        return [
            "composite_biases" => $composite_biases
        ];
    }

    public function polar_biases( $polar_biases ): Array
    {
        return [
            "polar_biases" => $polar_biases
        ];
    }

    public function polar_bias_by_factor( $polar_biases_by_factor ): Array
    {
        return [
            "polar_biases_by_factor" => $polar_biases_by_factor
        ];
    }

    public function composite_bias_by_factor( $composite_biases_by_factor ): Array
    {
        return [
            "composite_biases_by_factor" => $composite_biases_by_factor
        ];
    }


    public function assessmentTypeOverview( $current_polar_bias_percentage ): Array
    {
        return [
                "bias_percentage" => $current_polar_bias_percentage
        ];
    }

    public function assessmentTypePastAssessment( int $round, int $year, $percentage, $total_assessment = null ): Array
    {
        return [
                "round" => $round,
                "year" => $year,
                "label" => "A$round ($year)",
                "total_assessment_in_round" => $total_assessment, 
                "bias_percentage" => $percentage
        ];
    }

    public function FactorMeanAssessment( int $round, int $year, $mean, $total_assessment = null ): Array
    {
        return [
                "round" => $round,
                "year" => $year,
                "label" => "A$round ($year)",
                "total_assessment_in_round" => $total_assessment, 
                "factor_mean" => $mean
        ];
    }

    public function FactorBiasAssessment( int $round, int $year, $percentage, $total_assessment = null ): Array
    {
        return [
                "round" => $round,
                "year" => $year,
                "total_assessment_in_round" => $total_assessment, 
                "bias_percentage" => $percentage
        ];
    }

    public function assessmentTypeGender( $total_male, $male_counter, $total_female, $female_counter, $total_non_binary, $non_binary_counter, $data_type ): Array
    {
        if( $data_type == 'polar' ) {
            $male_percent = PercentageCalculation2($total_male, $male_counter);
            $female_percent = PercentageCalculation2($total_female, $female_counter);
            $non_binary_percent = PercentageCalculation2($total_non_binary, $non_binary_counter);
        }  
        else {
            $male_percent = PercentageCalculation($total_male, $male_counter);
            $female_percent = PercentageCalculation($total_female, $female_counter);
            $non_binary_percent = PercentageCalculation($total_non_binary, $non_binary_counter);
        }
        return [
            "gender" => [
                "MALE" => [
                    "type" => "MALE",
                    "label" => "Male",
                    "bias_percentage" => $male_percent
                ],
                "FEMALE" => [
                    "type" => "FEMALE",
                    "label" => "Female",
                    "bias_percentage" => $female_percent,
                ],
                "NON_BINARY" => [
                    "type" => "NON_BINARY",
                    "label" => "Non Binary",
                    "bias_percentage" => $non_binary_percent
                ]
            ]

        ];
    }

    public function albSorting( $data ) {
        usort( $data, function($a, $b) {
            return strnatcasecmp($a, $b );
        });

        return $data;
    }

    public function assessmentTypeAge( $age_counter, $count_type, $data_type ): Array
    {
        $ages = array_keys( $age_counter );
        $list = array();
        $ages = $this->albSorting( $ages );

        foreach( $ages as $age )
        {
            if( $data_type == 'polar' )
                $bias_percentage = PercentageCalculation2( $age_counter[$age]['total'], $age_counter[$age][$count_type] );
            else 
                $bias_percentage = PercentageCalculation( $age_counter[$age]['total'], $age_counter[$age][$count_type] );

            $list = array_merge( $list, [ $age => [
                    "type" => $age,
                    "label" => $age,
                    "bias_percentage" => $bias_percentage
            ] ] );
        }
        return [ 'age' => $list ];
    }

    public function assessmentTypeHouse( $house_counter, $count_type, $data_type ): Array
    {
        $houses = array_keys( $house_counter );
        $list = array();
        $houses = $this->albSorting( $houses );

        foreach( $houses as $house )
        {
            if( $data_type == 'polar' )
                $bias_percentage = PercentageCalculation2( $house_counter[$house]['total'], $house_counter[$house][$count_type] );
            else 
                $bias_percentage = PercentageCalculation( $house_counter[$house]['total'], $house_counter[$house][$count_type] );

            $list = array_merge( $list, [ $house => [
                    "type" => $house,
                    "label" => $house,
                    "bias_percentage" => $bias_percentage
            ] ] );
        }
        return [ 'house' => $list ];
    }

    public function assessmentTypeTutorGroup( $tutor_group_counter, $count_type, $data_type ): Array
    {
        $tutor_groups = array_keys( $tutor_group_counter );
        $list = array();
        $tutor_groups = $this->albSorting( $tutor_groups );

        foreach( $tutor_groups as $tutor_group )
        {
            if( $data_type == 'polar' )
                $bias_percentage = PercentageCalculation2( $tutor_group_counter[$tutor_group]['total'], $tutor_group_counter[$tutor_group][$count_type] );
            else 
                $bias_percentage = PercentageCalculation( $tutor_group_counter[$tutor_group]['total'], $tutor_group_counter[$tutor_group][$count_type] );

            $list = array_merge( $list, [ $tutor_group => [
                    "type" => $tutor_group,
                    "label" => $tutor_group,
                    "bias_percentage" => $bias_percentage
            ] ] );
        }
        return [ 'tutor_group' => $list ];

    }

    public function polarBiasesByFactor( $total, $polar_bias_list )
    {
        return [
            "POLAR_LOW_SELF_DISCLOSURE" => [
                "label" => "Polar Low Self Disclosure",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_LOW_SELF_DISCLOSURE'] )
            ],
            "POLAR_HIGH_SELF_DISCLOSURE" => [
                "label" => "Polar High Self Disclosure",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_HIGH_SELF_DISCLOSURE'] )
            ],
            "POLAR_LOW_TRUST_OF_SELF" => [
                "label" => "Polar Low Trust Of Self",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_LOW_TRUST_OF_SELF'] )
            ],
            "POLAR_HIGH_TRUST_OF_SELF" => [
                "label" => "Polar High Trust Of Self",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_HIGH_TRUST_OF_SELF'] )
            ],
            "POLAR_LOW_TRUST_OF_OTHERS" => [
                "label" => "Polar Low Trust Of Others",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_LOW_TRUST_OF_OTHERS'] )
            ],
            "POLAR_HIGH_TRUST_OF_OTHERS" => [
                "label" => "Polar High Trust Of Others",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_HIGH_TRUST_OF_OTHERS'] )
            ],
            "POLAR_LOW_SEEKING_CHANGE" => [
                "label" => "Polar Low Seeking Change",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_LOW_SEEKING_CHANGE'] )
            ],
            "POLAR_HIGH_SEEKING_CHANGE" => [
                "label" => "Polar High Seeking Change",
                "bias_percentage" => PercentageCalculation( $total, $polar_bias_list['POLAR_HIGH_SEEKING_CHANGE'] )
            ],
        ];
    }

    public function compositeBiasesByFactor( $total, $composite_bias_list )
    {
        return [
            "SOCIAL_NAIVETY" => [
                "label" => "Social Naivety",
                "bias_percentage" => PercentageCalculation( $total, $composite_bias_list['SOCIAL_NAIVETY'] )
            ],
            "HIDDEN_AUTONOMY" => [
                "label" => "Hidden Autonomy",
                "bias_percentage" => PercentageCalculation( $total, $composite_bias_list['HIDDEN_AUTONOMY'] )
            ],
            "HIDDEN_VULNERABILITY" => [
                "label" => "Hidden Vulnerability",
                "bias_percentage" => PercentageCalculation( $total, $composite_bias_list['HIDDEN_VULNERABILITY'] )
            ],
            "OVER_REGULATION" => [
                "label" => "Over Regulation",
                "bias_percentage" => PercentageCalculation( $total, $composite_bias_list['OVER_REGULATION'] )
            ],
            "SEEKING_CHANGE_INSTABILITY" => [
                "label" => "Seeking Change Instability",
                "bias_percentage" => PercentageCalculation( $total, $composite_bias_list['SEEKING_CHANGE_INSTABILITY'] )
            ]
        ];
    }

}

