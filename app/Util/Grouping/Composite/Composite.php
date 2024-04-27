<?php

namespace App\Util\Grouping\Composite;

use App\Util\Builder\Composite\CompositeBuilder;
use App\Util\Grouping\Composite\CompositeBase;
use App\Util\Builder\Safeguarding\SafeguardingBuilder;
use DateTime;
use stdClass;

class Composite extends CompositeBase
{
    private $risk;
    private $label;
    private $score;

    public function __construct()
    {
        $this->compositeBuilder = new CompositeBuilder();
        $this->safeguardingBuilder = new SafeguardingBuilder(); 
        //parent::__construct();
    }
    public function compositeList(): Array
    {
        return [
            'Social Naivety',
            'Hidden Vulnerability',
            'Hidden Autonomy',
            'Over Regulation',
            'Seeking Change Instability'
        ];
    }

    public function compositeBiasObject() {
         return [
            "SOCIAL_NAIVETY" => 0,
            "HIDDEN_AUTONOMY" => 0,
            "HIDDEN_VULNERABILITY" => 0,
            "OVER_REGULATION" => 0,
            "SEEKING_CHANGE_INSTABILITY" => 0
        ];
    }

    public function studentCompositeList($scoredata): Array
    {
        $in_school = isset( $scoredata['con_data']['type'] ) ? (object)$scoredata['con_data'] : null;

        $out_of_school = isset( $scoredata['gen_data']['type'] ) ? (object)$scoredata['gen_data'] : null;
        if( $in_school != null )
        {
            $in_school->SELF_DISCLOSURE = isset( $in_school->sd_data['score'] ) ? (float)$in_school->sd_data['score'] : null;
            $in_school->TRUST_OF_SELF = isset( $in_school->tos_data['score'] ) ?  (float)$in_school->tos_data['score'] : null;
            $in_school->TRUST_OF_OTHERS = isset( $in_school->too_data['score'] ) ?  (float)$in_school->too_data['score'] : null;
            $in_school->SEEKING_CHANGE = isset( $in_school->sc_data['score'] ) ? (float)$in_school->sc_data['score'] : null;
        }
        if( $out_of_school != null )
        {
            $out_of_school->SELF_DISCLOSURE = isset( $out_of_school->sd_data['score'] ) ? (float)$out_of_school->sd_data['score'] : null;
            $out_of_school->TRUST_OF_SELF = isset( $out_of_school->tos_data['score'] ) ? (float)$out_of_school->tos_data['score'] : null;
            $out_of_school->TRUST_OF_OTHERS = isset( $out_of_school->too_data['score'] ) ? (float)$out_of_school->too_data['score'] : null;
            $out_of_school->SEEKING_CHANGE = isset( $out_of_school->sc_data['score'] ) ? (float)$out_of_school->sc_data['score'] : null;
        
        }
        if( $in_school != null) {
            list( 'risks' => $risks ) = $this->StudentCompositeRisksObject( $in_school, $in_school->imp_rawdata, 'IN_SCHOOL', [] );
            $composite['IN_SCHOOL'] = $risks;
        }
        if( $out_of_school != null) {
            list( 'risks' => $risks ) = $this->StudentCompositeRisksObject( $out_of_school, $out_of_school->imp_rawdata, 'OUT_OF_SCHOOL', [] );
            $composite['OUT_OF_SCHOOL'] = $risks;
        }
       
        if ($scoredata['risk_sci'] == '1') {
            $composite['IN_SCHOOL'][] = $this->safeguardingBuilder->risk('Seeking Change Instability');
            $composite['OUT_OF_SCHOOL'][] = $this->safeguardingBuilder->risk('Seeking Change Instability');
        }
            
        return isset( $composite ) ? $composite : [];
    }

    public function newstudentCompositeList($in_school, $out_of_school): Array
    {
        if( $in_school != null )
        {
            $in_school->imp_rawdata = RawDataArray( $in_school );
            $in_school = ModifyScoreData( $in_school );
            list( 'risks' => $risks ) = $this->StudentCompositeRisksObject( $in_school, $in_school->imp_rawdata, 'IN_SCHOOL', [] );
            $composite['IN_SCHOOL'] = $risks;
        }
        if( $out_of_school != null )
        {
            $out_of_school->imp_rawdata = RawDataArray( $out_of_school );
            $out_of_school = ModifyScoreData( $out_of_school );
            list( 'risks' => $risks ) = $this->StudentCompositeRisksObject( $out_of_school, $out_of_school->imp_rawdata, 'OUT_OF_SCHOOL', [] );
            $composite['OUT_OF_SCHOOL'] = $risks;
        }

        if( $in_school && $out_of_school ) {
            list( 'risks' => $risks ) = $this->StudentSCICompositeRisksObject( $in_school, $out_of_school, 
                                                    $in_school->imp_rawdata, $out_of_school->imp_rawdata,
                                                    [] );
            if ( count($risks) > 0 ) {
                $composite['IN_SCHOOL'][] = $this->safeguardingBuilder->risk('Seeking Change Instability');
                $composite['OUT_OF_SCHOOL'][] = $this->safeguardingBuilder->risk('Seeking Change Instability');
            }                                        
        }
            
        return isset( $composite ) ? $composite : [];
    }

    public function BuildComposite($data): Array
    {
        $scoredata = $data[ count($data) - 1 ] ?? null;
        if( !$scoredata )
            abort(400, 'No score found');
        $composite = $this->studentCompositeList($scoredata);

        if( count($composite) == 0 )
            return $composite;
            
        // IN SCHOOL
        $in_school_risks = array_column( isset( $composite['IN_SCHOOL'] ) ? $composite['IN_SCHOOL'] : [], 'label');
        // OUT OF SCHOOL
        $out_of_school_risks = array_column( isset( $composite['OUT_OF_SCHOOL'] ) ? $composite['OUT_OF_SCHOOL'] : [], 'label');

        $composite_biases = [];
        foreach($in_school_risks as $label) 
            $composite_biases['IN_SCHOOL'][] = $this->compositeBuilder->pupilcohortcomposite($label, $scoredata);

        foreach($out_of_school_risks as $label) 
            $composite_biases['OUT_OF_SCHOOL'][] = $this->compositeBuilder->pupilcohortcomposite($label, $scoredata);    

        return $composite_biases;
    }

    public function newBuildComposite( $in_school, $out_of_school ): Array
    {
        $composite = $this->newstudentCompositeList( $in_school, $out_of_school );

        if( count($composite) == 0 )
            return $composite;
            
        // IN SCHOOL
        $in_school_risks = array_column( isset( $composite['IN_SCHOOL'] ) ? $composite['IN_SCHOOL'] : [], 'label');
        // OUT OF SCHOOL
        $out_of_school_risks = array_column( isset( $composite['OUT_OF_SCHOOL'] ) ? $composite['OUT_OF_SCHOOL'] : [], 'label');
        if( $in_school ) {
            $date = new DateTime( $in_school->datetime );
            $date = $date->format('d.m.Y');
        }else {
            $date = new DateTime( $out_of_school->datetime );
            $date = $date->format('d.m.Y');
        }
       
        $composite_biases = [];
        foreach($in_school_risks as $label) 
            $composite_biases['IN_SCHOOL'][] = $this->compositeBuilder->pupilcohortcomposite($label, $date);

        foreach($out_of_school_risks as $label) 
            $composite_biases['OUT_OF_SCHOOL'][] = $this->compositeBuilder->pupilcohortcomposite($label, $date);    
 
        return $composite_biases;
    }

    public function updateCompositeRiskWithActionPlan($composite_risks, $actionPlans)
    {
        //Update Composite risk object with action plan if exist
        foreach($actionPlans as $actionPlan) {
            $biastype = BiasAbreviationToName($actionPlan->bias);
            foreach ($composite_risks as $key => $value) {
                if ($value['label'] == $biastype) {
                    $composite_risks[$key]['action_plan'] = $this->cohortBuilder->action_plan($actionPlan);
                }
            }
        }
        return $composite_risks;
    }

    public function calculate_OR( $RAWDATA, $type, $risks ){
        $common_raw_exp = explode(',', $RAWDATA );
        if (count($common_raw_exp) > 0) {
            $raw_common_sum = array_sum( $common_raw_exp );
            $raw_common_mean = (float)$raw_common_sum / count( $common_raw_exp );
            $raw_common_variance = 0;
            foreach ( $common_raw_exp as $raw_common ) {
                $raw_common_variance += pow( (float)$raw_common - (float)$raw_common_mean, 2);
            }
            $raw_common_variance /= ( false ? count( $common_raw_exp ) - 1 : count( $common_raw_exp ));

            if (($raw_common_mean >= 3.01 && $raw_common_mean <= 3.99) && ($raw_common_variance >= 0.15 && $raw_common_variance <= 0.75)) {
                if ($raw_common_variance >= 0.2 && $raw_common_variance <= 0.75) {
                    $type = ucwords ( strtolower ( str_replace('_', ' ', $type) ) );
                    $risks[] = $this->safeguardingBuilder->risk('Over Regulation');
                }
            }
            return $risks;
        }
    }

    public function custom_calculate_OR( $RAWDATA, $type, $risks ){
        $common_raw_exp = $RAWDATA;

        if (count($common_raw_exp) > 0) {
            $raw_common_sum = array_sum( $common_raw_exp );
            $raw_common_mean = $raw_common_sum / count( $common_raw_exp );
            $raw_common_variance = 0;
            foreach ( $common_raw_exp as $raw_common ) {
                $raw_common_variance += pow($raw_common - $raw_common_mean, 2);
            }
            $raw_common_variance /= ( false ? count( $common_raw_exp ) - 1 : count( $common_raw_exp ));

            if (($raw_common_mean >= 3.01 && $raw_common_mean <= 3.99) && ($raw_common_variance >= 0.15 && $raw_common_variance <= 0.75)) {
                if ($raw_common_variance >= 0.2 && $raw_common_variance <= 0.75) {
                    $type = ucwords ( strtolower ( str_replace('_', ' ', $type) ) );
                    $risks[] = $this->safeguardingBuilder->risk('Over Regulation');
                }
            }
            return $risks;
        }
    }

    public function StudentCompositeRisksObject(object $data, string $rawdata, string $assessment_type, $risks): array
    {
        if ( (   $data->SELF_DISCLOSURE >= 1.5 && $data->TRUST_OF_SELF >= 9 &&
                    $data->TRUST_OF_OTHERS >= 9 && $data->SEEKING_CHANGE >= 9)) {
            $risks[] = $this->safeguardingBuilder->risk('Social Naivety');
        }


        if (    ( $data->SEEKING_CHANGE >= 0 && $data->SEEKING_CHANGE <= 15)
                && ( $data->SELF_DISCLOSURE <= 5.25 && $data->TRUST_OF_SELF <= 5.25 &&
                     $data->TRUST_OF_OTHERS <= 5.25) ) {
            $risks[] = $this->safeguardingBuilder->risk('Hidden Vulnerability');
        }

        if ( ( $data->SELF_DISCLOSURE <= 3.75 && $data->TRUST_OF_SELF >= 10.25
               && $data->TRUST_OF_OTHERS <= 3.75 && $data->SEEKING_CHANGE <= 4.5) ) {
            $risks[] = $this->safeguardingBuilder->risk('Hidden Autonomy');
        }

        if ( isset($rawdata) ) {
            $risks = $this->calculate_OR( $rawdata,  $assessment_type, $risks );

        }

        return [ 'risk_count' => count($risks), 'risks' => $risks ];
    }

    public function StudentHasCompositeRisksObject(object $data, string $rawdata, string $assessment_type): bool
    {
        $risks = [];
        if ( ( $data->SELF_DISCLOSURE >= 1.5 && $data->TRUST_OF_SELF >= 9 && 
                    $data->TRUST_OF_OTHERS >= 9 && $data->SEEKING_CHANGE >= 9)) {
            $risks[] = $this->safeguardingBuilder->risk('Social Naivety');
        }


        if ( ( $data->SEEKING_CHANGE >= 0 && $data->SEEKING_CHANGE <= 15) 
                && ( $data->SELF_DISCLOSURE <= 5.25 && $data->TRUST_OF_SELF <= 5.25 && 
                     $data->TRUST_OF_OTHERS <= 5.25) ) {
            $risks[] = $this->safeguardingBuilder->risk('Hidden Vulnerability');
        }

        if ( ( $data->SELF_DISCLOSURE <= 3.75 && $data->TRUST_OF_SELF >= 10.25 &&
               $data->TRUST_OF_OTHERS <= 3.75 && $data->SEEKING_CHANGE <= 4.5) ) {
            $risks[] = $this->safeguardingBuilder->risk('Hidden Autonomy');       
        }

        if ( isset($rawdata) ) {
            $risks = $this->calculate_OR( $rawdata,  $assessment_type, $risks );

        }

        return count($risks) > 0 ? true : false;   
    }

    public function StudentSCICompositeRisksObject( object $data_in_school, object $data_out_of_school, 
                                                    string $in_school_rawdata, string $out_of_school_rawdata,
                                                    array $risks ): array
    {
        if (( $data_out_of_school->SEEKING_CHANGE >= 11.25 && $data_in_school->SEEKING_CHANGE <= 4.5)
               || ($data_out_of_school->SEEKING_CHANGE  <= 4.5 && $data_in_school->SEEKING_CHANGE >= 11.25)) {
            $risks[] = $this->safeguardingBuilder->risk('Seeking Change Instability');
        }
       
        if( isset($data_out_of_school->SEEKING_CHANGE['value']) && isset($data_in_school->SEEKING_CHANGE['value']) ) {
            if (( $data_out_of_school->SEEKING_CHANGE['value'] >= 11.25 && $data_in_school->SEEKING_CHANGE['value'] <= 4.5)
                || ($data_out_of_school->SEEKING_CHANGE['value']  <= 4.5 && $data_in_school->SEEKING_CHANGE['value'] >= 11.25)) {
                $risks[] = $this->safeguardingBuilder->risk('Seeking Change Instability');
            }
        }

        return [ 'risk_count' => count($risks), 'risks' => $risks ];
    }

    public function ExtractOnlyCompositeBias( array $composite_biases ): Array
    {
        foreach( $composite_biases as $key => $value ) {
            unset( $composite_biases[$key]['description'] );
        }
        return isset($composite_biases[0]) ? $composite_biases[0] : null;
    }

    public function compositeBiasesCount( $school_data, $request, $student_grouping, $assessment_type, $second_school_data ): Array
    {
        $student_counter = 0;
        $male_counter = 0;
        $female_counter = 0;
        $non_binary_counter = 0;
        list( 'group' => $group, 'group_raw' => $group_raw ) = $student_grouping;
        $houses = houseObject( $group['house'] );
        $ages = ageObject( $group['year_group'] );
        $tutor_groups = tutorgroupObject( $group['tutor_group'] );
        //$total_male = CountGroupData($school_data, 'gender', 'm');
        //$total_female = CountGroupData($school_data, 'gender', 'f');
        //$total_non_binary = CountNonBinaryGroupData($school_data, 'gender');
        list ( 'group_total' => $group_total, 'total_non_binary' => $total_non_binary, 
        'total_male' => $total_male, 'total_female' => $total_female ) = CountGroupData( $school_data );
        $risk_count = 0;
        $student_ids = array_column( $school_data, 'student_id' );   
        list( 'houses' => $houses, 'ages' => $ages, 'tutor_groups' => $tutor_groups ) = CountByGroups( $student_ids, $houses, $ages, $tutor_groups, $group_raw, 'total', $request );  
        foreach( $school_data as $data)
        {
           $data = ModifyScoreData( $data ); 
           $rawdata = RawDataArray( $data );
           list( 'risk_count' => $risk_count ) = $this->StudentCompositeRisksObject( $data, $rawdata, $assessment_type, [] ); 
           $index =  GetDatabyMainId( $second_school_data, $data->ass_main_id, $assessment_type );
           if( $index > -1 && $risk_count == 0 ) {
                $second_school_data_object = ModifyScoreData( $second_school_data[$index] ); 
                $second_rawdata = RawDataArray( $second_school_data_object );
                if( $assessment_type == "IN_SCHOOL" )
                    list( 'risk_count' => $risk_count ) = $this->StudentSCICompositeRisksObject( $data, $second_school_data_object, $rawdata, $second_rawdata, [] );
                else 
                    list( 'risk_count' => $risk_count ) = $this->StudentSCICompositeRisksObject( $second_school_data_object, $data, $second_rawdata, $rawdata, [] );
           }
           //$houses = CountByHouse( $data->student_id, $houses, $group_raw, 'total' );
           //$ages = CountByYearGroup( $data->student_id, $ages, $group_raw, 'total' );
           //$tutor_groups = CountByTutorGroup( $data->student_id, $tutor_groups, $group_raw, 'total' );
           list( 'houses' => $houses, 'ages' => $ages, 'tutor_groups' => $tutor_groups ) = CountByGroups( [ $data->student_id ], $houses, $ages, $tutor_groups, $group_raw, 'total', $request );
           if($risk_count > 0 ) {
                $student_counter++;
                if ( $request->has('type') && $request->get('type') == 'gender' )
                {
                    list( 'male_counter' => $male_counter, 
                          'female_counter' => $female_counter, 
                          'non_binary_counter' => $non_binary_counter ) = CountByGender( $data->gender, $male_counter, $female_counter, $non_binary_counter );
                }
                else if( $request->has('type') ) {
                    list( 'houses' => $houses, 'ages' => $ages, 
                          'tutor_groups' => $tutor_groups ) = CountByGroups( $data->student_id, $houses, $ages, $tutor_groups, $group_raw, 'count', $request );
                }
           }
              
        }
        return  [ 
                 'male_counter' => $male_counter, 'student_counter' => $student_counter, 'total_student_counter' => count( $school_data ),
                 'female_counter' => $female_counter, 'non_binary_counter' => $non_binary_counter,
                 'house_counter' => $houses, 'age_counter' => $ages, 'tutor_group_counter' => $tutor_groups,
                 'total_male' => $total_male, 'total_female' => $total_female, 'total_non_binary' => $total_non_binary
                ];    
    }

    public function compositeBiasCountByFactor( $data, $assessment_type, $second_school_data, $countObject ): Array
    {
           $rawdata = RawDataArray( $data );
           list( 'risks' => $risks ) = $this->StudentCompositeRisksObject( $data, $rawdata, $assessment_type, [] );
           $other_composite_biases = array_column( $risks, 'type' );
           $composite_biases = [];
           $sci_composite_biases = [];
           $index =  GetDatabyMainId( $second_school_data, $data->ass_main_id, $assessment_type );
           if( $index > -1 ) {
                $second_school_data_object = ModifyScoreData( $second_school_data[$index] ); 
                $second_rawdata = RawDataArray( $second_school_data_object );
                if( $assessment_type == "IN_SCHOOL" )
                    list( 'risks' => $risks ) = $this->StudentSCICompositeRisksObject( $data, $second_school_data_object, $rawdata, $second_rawdata, [] );
                else 
                    list( 'risks' => $risks ) = $this->StudentSCICompositeRisksObject( $second_school_data_object, $data, $second_rawdata, $rawdata, [] );
                $sci_composite_biases = array_column( $risks, 'type' );
           }
           $composite_biases = array_merge( $other_composite_biases, $sci_composite_biases );

           
           if( in_array('SOCIAL_NAIVETY', $composite_biases ) )
              $countObject["SOCIAL_NAIVETY"]++;
           if( in_array('HIDDEN_AUTONOMY', $composite_biases ) )
              $countObject["HIDDEN_AUTONOMY"]++; 
           if( in_array('HIDDEN_VULNERABILITY', $composite_biases ) )
              $countObject["HIDDEN_VULNERABILITY"]++;
           if( in_array('OVER_REGULATION', $composite_biases ) )
              $countObject["OVER_REGULATION"]++;     
           if( in_array('SEEKING_CHANGE_INSTABILITY', $composite_biases ) )
              $countObject["SEEKING_CHANGE_INSTABILITY"]++;       
        //}
        return $countObject;
    }
}
