<?php
namespace App\Services\Safeguarding;
use App\Models\Dbglobal\Model_dat_schools;
use App\Util\Grouping\RoundManagement\Round;
use App\Models\Dbschools\Model_ass_main;
use App\Services\CohortDataFilterServiceProvider;
use App\Util\Grouping\Composite\Composite;
use App\Util\Builder\Safeguarding\SafeguardingBuilder;
use DB;

class ResponseServiceProvider
{
    public function __construct()
    {
        $this->datSchool = new Model_dat_schools();
    }

    public function RiskList( $polarRisks, $compositeRisks, $flag, $name ) {
        $risks = array_merge( $polarRisks, $compositeRisks );
        $riskList = [];
        foreach ($risks as $key => $risk) {
            $updateRisk = ( new SafeguardingBuilder )->risk($risk);
            $updateRisk['flagged_history'] = $flag[$risk] ?? [];
            $updateRisk['flagged_information'] = $this->flagInformation( $flag[$risk] ?? [], $name, $risk );
            $riskList[] = $updateRisk;
        }
        return $riskList;
    }

    public function flagInformation( $flagArray, $name, $risk ) {
        $counter = 0;
        foreach ($flagArray as $key => $value) {
            if( $value == 1 ) $counter++;
            else break;
        }
        return "$name has been flagged with $risk for $counter out of ".count($flagArray)." assessment(s)";
    }

    public function flag( $currpolarRisks, $currcompositeRisks, $past_assessment) {
        $curr_risks = array_merge( $currpolarRisks, $currcompositeRisks );
        foreach ($curr_risks as $key => $risk) {
            $flag[$risk][] = 1;
            foreach( $past_assessment as $key2 => $past_ass) {
                $past_risks = array_merge( $past_ass['data']['polarRisks'] ?? [], $past_ass['data']['compositeRisks'] ?? []);
                if( in_array( $risk, $past_risks ) ) $flag[$risk][] = 1;
                else $flag[$risk][] = 0;
            }
        }
        return $flag ?? [];
    }

    public function selectIndicator($type, $assessment) {
        if( $type == null)
            return $assessment['indicator'];
        if( $type == 'or' ) {
            if( in_array('Over Regulation', $assessment['data']['compositeRisks'] ) ) 
                return 3;
            return 1;
        }
        if( $type == 'sci' ) {
            if( in_array('Seeking Change Instability', $assessment['data']['compositeRisks'] ) )  
                return 3;
            return 1;
        }
    }

    public function assessmentResponse( $data, $type = null ) {
        $flag = $this->flag( $data['current_assessment']['data']['polarRisks'] ?? [],
                                 $data['current_assessment']['data']['compositeRisks'] ?? [],
                                 $data['past_assessment'] ?? [] );

        if( isset($data['current_assessment'] ) ) {
            $current_assessment = [
                'original_indicator' => $data['current_assessment']['indicator'],
                'indicator' => $this->selectIndicator( $type, $data['current_assessment'] ),
                'year' => $data['current_assessment']['year'],
                'round' => $data['current_assessment']['round'],
                'scibias' => $data['current_assessment']['scibias'],
                'risks' => $this->RiskList( $data['current_assessment']['data']['polarRisks'],
                                            $data['current_assessment']['data']['compositeRisks'],
                                            $flag, $data['current_assessment']['data']['student']['name'] ?? null
                                        )
            ];
        }
        $data['past_assessment'] = $data['past_assessment'] ?? [];
        foreach ($data['past_assessment'] as $key => $ass) {
            $past_assessments[] = [
                'original_indicator' => $ass['indicator'],
                'indicator' => $this->selectIndicator( $type, $ass ),
                'year' => $ass['year'],
                'round' => $ass['round'],
                'scibias' => $data['current_assessment']['scibias'],
                'risks' => $this->RiskList( $ass['data']['polarRisks'], $ass['data']['compositeRisks'], null,
                                            $data['current_assessment']['data']['student']['name'] ?? null )
            ];
        }

        return [ 'current_assessment' => $current_assessment ?? null, 'past_assessments' => $past_assessments ?? [] ];
    }

    public function Category( $risk, $risktype, $assessment_type ) {
        if( $risktype == 'has_significant_increase_in_risk' ) {
            if( $assessment_type == 'IN_SCHOOL') return 2;
            if( $assessment_type == 'OUT_OF_SCHOOL') return 1;
            // if( $in_risk && !$out_risk ) return 2;
            // if( !$in_risk && $out_risk ) return 1;
        }
        else if( $risktype == 'sustained_risk' ) {
            if( $assessment_type == 'IN_SCHOOL') return 5;
            if( $assessment_type == 'OUT_OF_SCHOOL') return 4;
            //if( $in_risk && $out_risk ) return 6;
            // if( $in_risk && !$out_risk ) return 5;
            // if( !$in_risk && $out_risk ) return 4;
        }
        else if( $risktype == 'sustained_over_regulation' ) {
            if( $assessment_type == 'IN_SCHOOL') return 8;
            if( $assessment_type == 'OUT_OF_SCHOOL') return 7;
            // if( $in_risk && !$out_risk ) return 8;
            // if( !$in_risk && $out_risk ) return 7;
        }
        else if( $risktype == 'sustained_sci' ) {
            if( $assessment_type == 'IN_SCHOOL') return 11;
            if( $assessment_type == 'OUT_OF_SCHOOL') return 10;
            // if( $in_risk && !$out_risk ) return 11;
            // if( !$in_risk && $out_risk ) return 10;
        }
    }

    public function response( $school_data ) {
        [
            "in_school_student" => $in_school_student,
            "out_of_school_student" => $out_of_school_student
        ] = $school_data;

        $in_school_roundYear = array_keys($in_school_student);
        $in_school_sci_assessment_count = $out_of_school_sci_assessment_count = 0;
        foreach ($in_school_roundYear as $key => $id) {
            $student_data =  $in_school_student[$id]['current_assessment'] ?? null;
            if( isset($in_school_student[$id]['has_significant_increase_in_risk']) )
            {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id]);
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null );
                $in_significant_increase_in_risk[] = [
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'in_school_category_significant_increase_in_risk' => $this->Category( $in_school_student[$id]['has_significant_increase_in_risk'],
                                                   'has_significant_increase_in_risk', 'IN_SCHOOL' )
                ];
            }
            if( isset($in_school_student[$id]['sustained_risk']) ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id]);
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null );
                $in_sustained_risk[] = [
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'in_school_category_sustained_risks' => $this->Category( $in_school_student[$id]['sustained_risk'],
                                                   'sustained_risk', 'IN_SCHOOL' )
                ];
            }
            if( isset($in_school_student[$id]['sustained_over_regulation']) ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id], 'or');
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null, 'or' );
                $in_sustained_over_regulation[] = [
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'in_school_category_sustained_over_regulation' => $this->Category( $in_school_student[$id]['sustained_over_regulation'], 
                                                   'sustained_over_regulation', 'IN_SCHOOL'  )
                ];
            }
            if( isset($in_school_student[$id]['sustained_sci']) && $in_school_student[$id]['current_assessment']['scibias'] ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id], 'sci');
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null, 'sci' );
                $in_sustained_sci[] = [
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'in_school_category_seeking_change_instability' => $this->Category( $in_school_student[$id]['sustained_sci'],
                                                   'sustained_sci', 'IN_SCHOOL' )
                ];
            }
            if( isset($in_school_student[$id]['sustained_sci']) && $in_school_student[$id]['current_assessment']['ungroupedscibias'] ) {
                $in_school_sci_assessment_count = $in_school_sci_assessment_count + 1;
            }
        }

        $out_of_school_roundYear = array_keys($out_of_school_student);
        foreach ($out_of_school_roundYear as $key => $id) {
            $student_data =  $out_of_school_student[$id]['current_assessment'] ?? null;
            if( isset($out_of_school_student[$id]['has_significant_increase_in_risk']) )
            {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id] ?? null);
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null );
                $out_significant_increase_in_risk[] = [ 
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'out_of_school_category_significant_increase_in_risk' => $this->Category( $out_of_school_student[$id]['has_significant_increase_in_risk'], 
                                                   'has_significant_increase_in_risk', 'OUT_OF_SCHOOL' )
                ];
            }
            if( isset($out_of_school_student[$id]['sustained_risk']) ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id] ?? null);
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null );
                $out_sustained_risk[] = [ 
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'out_of_school_category_sustained_risks' => $this->Category( $out_of_school_student[$id]['sustained_risk'],
                                                   'sustained_risk', 'OUT_OF_SCHOOL' )
                ];
            }
            if( isset($out_of_school_student[$id]['sustained_over_regulation']) ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id] ?? null, 'or');
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null, 'or' );
                $out_sustained_over_regulation[] = [ 
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'out_of_school_category_sustained_over_regulation' => $this->Category( $out_of_school_student[$id]['sustained_over_regulation'],
                                                   'sustained_over_regulation', 'OUT_OF_SCHOOL' )
                ];
            }
            if( isset($out_of_school_student[$id]['sustained_sci']) && $out_of_school_student[$id]['current_assessment']['scibias'] ) {
                $in_assessment = $this->assessmentResponse( $in_school_student[$id] ?? null, 'sci');
                $out_assessment = $this->assessmentResponse( $out_of_school_student[$id] ?? null, 'sci' );
                $out_sustained_sci[] = [ 
                    'student' => $student_data['data']['student'] ?? null,
                    'assessment' => [ 'in_school' => $in_assessment, 'out_of_school' => $out_assessment],
                    'out_of_school_category_seeking_change_instability' => $this->Category( $out_of_school_student[$id]['sustained_sci'],
                                                   'sustained_sci', 'OUT_OF_SCHOOL' )
                ];
            }
            if( isset($out_of_school_student[$id]['sustained_sci']) && $out_of_school_student[$id]['current_assessment']['ungroupedscibias'] ) {
                $out_of_school_sci_assessment_count = $out_of_school_sci_assessment_count + 1;
            }
        }

        return [
            'significant_increase_in_risk' => [ 'in_school' => $in_significant_increase_in_risk ?? [], 
                                                'out_of_school' => $out_significant_increase_in_risk ?? [], 
                                                'in_school_assessment_count' => count($in_significant_increase_in_risk ?? []),
                                                'out_of_school_assessment_count' => count($out_significant_increase_in_risk ?? []),
                                             ],
            'sustained_risks' => [ 'in_school' => $in_sustained_risk ?? [], 
                                    'out_of_school' => $out_sustained_risk ?? [],
                                    'in_school_assessment_count' => count($in_sustained_risk ?? []),
                                    'out_of_school_assessment_count' => count($out_sustained_risk ?? []),
                                ],
            'sustained_over_regulation' => [ 'in_school' => $in_sustained_over_regulation ?? [], 
                                             'out_of_school' => $out_sustained_over_regulation ?? [] ,
                                             'in_school_assessment_count' => count($in_sustained_over_regulation ?? []),
                                             'out_of_school_assessment_count' => count($out_sustained_over_regulation ?? []),
                                            ],
            'seeking_change_instability' => [ 'in_school' => $in_sustained_sci ??  [], 
                                              'out_of_school' => $out_sustained_sci ?? [],
                                              'in_school_assessment_count' => $in_school_sci_assessment_count,
                                              'out_of_school_assessment_count' => $out_of_school_sci_assessment_count,
                                            ],
         ];
    }
}
