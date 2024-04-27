<?php

namespace App\Util\Builder\Rag;

use App\Util\Builder\Rag\Builder;
use DateTime;
use Illuminate\Support\Facades\Auth;
use App\Util\Grouping\User\User;
use App\Util\Grouping\PolarRisk\PolarRisk;
use App\Util\Grouping\Composite\Composite;
use App\Services\RagPageServiceProvider;
use App\Models\Dbglobal\Model_dat_schools;

class RagBuilder extends Builder {
    public function __construct() {
        $this->polarRisk = new PolarRisk();
        $this->compositeBias = new Composite();
        $this->ragPageServiceProvider = new RagPageServiceProvider();
        $this->dat_school = new Model_dat_schools();
    }

    public function polar_bias_change( $value ) {
        if( !isset($value->diff_in_school_polar_count) && !isset( $value->diff_out_of_school_polar_count ) ) {
            return null;
        }
        $change = $value->diff_in_school_polar_count ?? $value->diff_out_of_school_polar_count;
        if( $change < 0 )
            return 'POSITIVE';
        else if( $change > 0 )
            return 'NEGATIVE';
        else
            return 'NO_CHANGE';
    }

    
    public function studentObject( object $value, array $builder, array $userList, bool $name_code_schl ): Array {
        $cid = $value->student_id.'-'.$value->completed_date;
        if( !in_array( $cid, $userList ) ) {
            $userList[] = $cid;
            $builder[] = [
                'uid' => $cid, 
                'polar_bias_change' =>  $this->polar_bias_change( $value ),
                'polar_bias' =>  $value->diff_in_school_polar_count ?? $value->diff_out_of_school_polar_count,
                'student' => [
                'id' => $value->student_id,
                'gender' => $value->gender,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'name' => $value->firstname.' '.$value->lastname,
                'name_code' => $name_code_schl == true ? ( $value->firstname.' '.$value->lastname) : getUserNameCode( $value ),
                'count_id' => $cid,
                'name_code_school' => $name_code_schl
                ]
            ];
        }
        $students = array_column( $builder, 'student' );
        $student_ids = array_column( $students, 'count_id' );
        $index = array_search( $cid, $student_ids );
        return [ 'builder' => $builder, 'pos' => $index , 'userList' => $userList ];
    }
    public function assessmentScore( $builder, $pos, $assessment_type, $value ): Array {
        $name = $builder[$pos]['student']['name_code_school'] == true ? $builder[$pos]['student']['name'] : $builder[$pos]['student']['name_code'];
        if( $builder[$pos]['student']['name_code_school'] == false ) {
            if( auth()->user()->level == 6 )
                $name = $builder[$pos]['student']['name_code'];
            else
                $name = $builder[$pos]['student']['name'];
        }
        $builder[$pos][$assessment_type] = [
            'assessment_score' => [
                'SELF_DISCLOSURE' => [ 
                    'value' => (float)$value->P,
                    'date' => $value->completed_date,
                    'description' => $this->ragPageServiceProvider->ragPageDescription($value->P, 'Self Disclosure', $name, $value->gender) 
                ],
                'TRUST_OF_SELF' => [ 
                    'value' => (float)$value->S, 
                    'date' => $value->completed_date,
                    'description' => $this->ragPageServiceProvider->ragPageDescription($value->S, 'Trust Of Self', $name, $value->gender) 
                ],
                'TRUST_OF_OTHERS' => [ 
                    'value' => (float)$value->L, 
                    'date' => $value->completed_date,
                    'description' => $this->ragPageServiceProvider->ragPageDescription($value->L, 'Trust Of Others', $name, $value->gender) 
                ],
                'SEEKING_CHANGE' => [ 
                    'value' => (float)$value->X, 
                    'date' => $value->completed_date,
                    'description' => $this->ragPageServiceProvider->ragPageDescription($value->X, 'Seeking Change', $name, $value->gender) 
                ]

            ]
        ];
        return $builder;
    }

    public function speedUpdate( $builder, $pos, $assessment_type, $speed ): Array {
        $builder[$pos]['assessment_information']['speed'] = $speed;
        return $builder;
    }
    public function speed( $builder, $pos, $assessment_type, $value ) {

        if( isset($builder[$pos]['assessment_information']['speed']) )
            return $builder;

        $packages = Packages( request()->school_id );
        //too fast
        if( in_array('safeguarding', $packages ) && $value->time <= '00:05:00' )
            $builder = $this->speedUpdate( $builder, $pos, $assessment_type, 'QUICKLY' );
        else if( !in_array('safeguarding', $packages ) && $value->time <= '00:03:00' )
            $builder = $this->speedUpdate( $builder, $pos, $assessment_type, 'QUICKLY' );
        //too slow
        else if( in_array('safeguarding', $packages ) && $value->time >= '00:15:40' )
            $builder = $this->speedUpdate( $builder, $pos, $assessment_type, 'SLOWLY' );
        else if( !in_array('safeguarding', $packages ) && $value->time >= '00:08:20' )
            $builder = $this->speedUpdate( $builder, $pos, $assessment_type, 'SLOWLY' );
        else
            $builder = $this->speedUpdate( $builder, $pos, $assessment_type, 'NORMAL' );

        return $builder;

    }

    public function priorityFlagUpdate( $builder, $pos, $assessment_type, $speed ): Array {
        $builder[$pos]['assessment_information']['priority'] = true;
        $builder[$pos]['assessment_information']['priority_count_source'] = $assessment_type;
        return $builder;
    }

    public function priorityFlag( $builder, $pos, $assessment_type, $value, $priorityList ) {
        $value = ModifyScoreData( $value );
        $count = $this->polarRisk->StudentPolarRisks( $value );
        if( $count >= 2 )
            $builder = $this->priorityFlagUpdate( $builder, $pos, $assessment_type, 1 );
        if( $assessment_type == 'IN_SCHOOL' && $count >= 2 )
            $priorityList['IN_SCHOOL'][] = $value->student_id;
        if( $assessment_type == 'OUT_OF_SCHOOL' && $count >= 2 )
            $priorityList['OUT_OF_SCHOOL'][] = $value->student_id;
        return [ 'priorityList' => $priorityList, 'builder' => $builder ];
    }

    public function compositeBiases( $builder, $pos, $assessment_type, $value ) {
        $value = ModifyScoreData( $value );
        $rawdata = RawDataArray($value );
        $compositeBiases = $this->compositeBias->StudentCompositeRisksObject( $value, $rawdata, $assessment_type, [] );
        $builder[$pos][$assessment_type]['composite_biases'] = $compositeBiases['risks'];
        //$builder[$pos][$assessment_type]['composite_biases'] = array_column( $compositeBiases['risks'], 'label' );
        
        if( $assessment_type == 'OUT_OF_SCHOOL' ) {
            if( isset($builder[$pos]['IN_SCHOOL']['assessment_score']) && isset($builder[$pos]['OUT_OF_SCHOOL']['assessment_score']) ) {
                $sci = $this->compositeBias->StudentSCICompositeRisksObject( (object)$builder[$pos]['IN_SCHOOL']['assessment_score'], (object)$builder[$pos]['OUT_OF_SCHOOL']['assessment_score'],
                                                 'in_school_rawdata', 'out_of_school_rawdata', [] );

                $sciBias = $sci['risks'];
                $builder[$pos]['IN_SCHOOL']['composite_biases'] = array_merge( $sciBias, $builder[$pos]['IN_SCHOOL']['composite_biases'] );
                $builder[$pos]['OUT_OF_SCHOOL']['composite_biases'] = array_merge( $sciBias, $builder[$pos]['OUT_OF_SCHOOL']['composite_biases'] );
            }
        }
        return $builder;
    }

    public function polarBiasesBuilder($label, $type, $bias_type, $date ) {
        return [
            "label" => $label,
            "type" => $type,
            "bias_type" => $bias_type,
            "date" => $date
        ];
    }

    public function polarBiases( $builder, $pos, $assessment_type, $value  ) {
        if( $value->P <= 3 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar Low Self Disclosure", 'POLAR_LOW_SELF_DISCLOSURE', 'SELF_DISCLOSURE', $value->completed_date );
        if ( $value->P >= 12 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar High Self Disclosure", 'POLAR_HIGH_SELF_DISCLOSURE', 'SELF_DISCLOSURE', $value->completed_date );
        
        if( $value->S <= 3 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar Low Trust Of Self", 'POLAR_LOW_TRUST_OF_SELF', 'TRUST_OF_SELF', $value->completed_date );
        if ( $value->S >= 12 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar High Trust Of Self", 'POLAR_HIGH_TRUST_OF_SELF', 'TRUST_OF_SELF', $value->completed_date );

        if( $value->L <= 3 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar Low Trust Of Others", 'POLAR_LOW_TRUST_OF_OTHERS', 'TRUST_OF_OTHERS', $value->completed_date );
        if ( $value->L >= 12 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar High Trust Of Others", 'POLAR_HIGH_TRUST_OF_OTHERS', 'TRUST_OF_OTHERS', $value->completed_date );

        if( $value->X <= 3 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar Low Seeking Change", 'POLAR_LOW_SEEKING_CHANGE', 'SEEKING_CHANGE', $value->completed_date );
        if ( $value->X >= 12 ) 
            $builder[$pos][$assessment_type]['polar_biases'][] = $this->polarBiasesBuilder("Polar High Seeking Change", 'POLAR_HIGH_SEEKING_CHANGE', 'SEEKING_CHANGE', $value->completed_date );

        return $builder;
    }

    public function manipulated( $builder, $pos, $assessment_type, $value ) {
        $builder[$pos]['assessment_information']['is_manipulated'] = $value->is_manipulated == '1' ? true : false;
        return $builder;
    }

    public function ObjectBuilder( $data, $builder, $userList, $assessment_type, $priorityList ): Array {
        $r = [];
        $name_code_schl = IsSchoolNameCode();
        foreach( $data as $value ) {
            list('builder' => $builder, 'pos' => $pos, 'userList' => $userList ) = $this->studentObject( $value, $builder, $userList, $name_code_schl );
            $builder = $this->assessmentScore( $builder, $pos, $assessment_type, $value );
            $builder = $this->compositeBiases( $builder, $pos, $assessment_type, $value );
            $builder = $this->polarBiases( $builder, $pos, $assessment_type, $value );
            //$builder = $this->speed( $builder, $pos, $assessment_type, $value );
            //list('priorityList' => $priorityList, 'builder' => $builder ) = $this->priorityFlag( $builder, $pos, $assessment_type, $value, $priorityList );
            //$builder = $this->manipulated( $builder, $pos, $assessment_type, $value );

        }
        return [ 'builder' => $builder, 'userList' => $userList, 'priorityList' => $priorityList ];
    }

    public function PriorityCount(  $builder, $in_priorityCount, $out_priorityCount ): Array {
        foreach( $builder as $key => $value ) {
            if( isset($value['assessment_information']['priority']) ) {
                if( $value['assessment_information']['priority'] == true ) {
                    if( $value['assessment_information']['priority_count_source'] == 'IN_SCHOOL' )
                        $builder[$key]['assessment_information']['priority_score'] = isset( $in_priorityCount[ $value['student']['id'] ] ) ? $in_priorityCount[ $value['student']['id'] ] : 1;
                    if( $value['assessment_information']['priority_count_source'] == 'OUT_OF_SCHOOL' )
                        $builder[$key]['assessment_information']['priority_score'] = isset( $out_priorityCount[ $value['student']['id'] ] ) ? $out_priorityCount[ $value['student']['id'] ] : 1;
                }
            }

        }
        return $builder;
    }

}
