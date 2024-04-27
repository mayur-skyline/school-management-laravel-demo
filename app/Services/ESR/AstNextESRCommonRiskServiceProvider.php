<?php
namespace App\Services\ESR;
use App\Models\Dbschools\Model_multischools;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbglobal\Model_groupdash;
use App\Models\Dbglobal\Model_groupRisk;
use App\Util\Grouping\RoundManagement\Round;
use App\Util\Grouping\CommonRisk\Risk;
use App\Models\Dbschools\Model_ass_main;
use App\Util\Grouping\Composite\Composite;
use App\Models\Dbglobal\Model_student_tracking_risks;
use DB;

class AstNextESRCommonRiskServiceProvider
{
    public function __construct()
    {
        $this->datSchool = new Model_dat_schools();
        $this->groupRisk = new Model_groupRisk();
        $this->commonRisk = new Risk();
        $this->round = new Round();
        $this->assMain = new Model_ass_main();
    }

    public function filter($year, $round) {
        $filter['campus'] = request()->get('campus') ?? request()->input('campus');
        $filter['academic_year'][] = $year;
        $filter['round'][] = $round;
        return $filter;
    }

    public function groupRisk($risks) {
        $riskList = null;
        foreach ($risks as $key => $riskObj) {
            if( !empty($riskList[$riskObj->risk]) )
                $riskList[$riskObj->risk] = $riskList[$riskObj->risk] + (int)$riskObj->count;
            else
                $riskList[$riskObj->risk] = (int)$riskObj->count;
        }
        return $riskList;
    }

    public function riskCategory( ) {
        $common_risk = $this->common_risk;
        if( $common_risk == 'LOW_SELF_DISCLOSURE' ) return 'polar';
        elseif( $common_risk == 'HIGH_SELF_DISCLOSURE' ) return 'polar';
        else if( $common_risk == 'LOW_TRUST_OF_SELF' ) return 'polar';
        else if( $common_risk == 'HIGH_TRUST_OF_SELF') return 'polar';
        else if( $common_risk == 'LOW_TRUST_OF_OTHERS' ) return 'polar';
        else if( $common_risk == 'HIGH_TRUST_OF_OTHERS' ) return 'polar';
        else if( $common_risk == 'LOW_SEEKING_CHANGE' ) return 'polar';
        else if( $common_risk == 'HIGH_SEEKING_CHANGE' ) return 'polar';
        else if( $common_risk == 'OVER_REGULATION' ) return 'composite';
        else if( $common_risk == 'HIDDEN_VULNERABILITY' ) return 'composite';
        else if( $common_risk == 'HIDDEN_AUTONOMY' ) return 'composite';
        else if( $common_risk == 'SEEKING_CHANGE_INSTABILITY' ) return 'composite';
        else return '';
    }

    public function fetchLastAssessments($filter) {
        $in_assessments = $out_assessments = null;
        $round = $filter['round'][0] ?? -1;
        $year =  $filter['academic_year'][0] ?? -1;
        $school_data = $this->assMain->getAssessmentReport($filter, ['hs', 'sch', 'at'], [], ['year'] );
        $school_house_data = $this->assMain->getAssessmentReport($filter, ['hs', 'sch', 'at'], [], ['house'] );
        $school_data = getINorOUTdata( $school_data );
        $school_house_data = getINorOUTdata( $school_house_data );

        $in_assessment = $school_data['IN_SCHOOL'];
        $in_house_assessment = $school_house_data['IN_SCHOOL'];
        $in_group_year = collect($in_assessment)->groupBy('value')->toArray();
        $in_group_house = collect($in_house_assessment)->groupBy('value')->toArray();

        $out_assessment = $school_data['OUT_OF_SCHOOL'];
        $out_house_assessment = $school_house_data['OUT_OF_SCHOOL'];
        $out_group_year = collect($out_assessment)->groupBy('value')->toArray();
        $out_group_house = collect($out_house_assessment)->groupBy('value')->toArray();

        //$in_assessments[$round.'-'.$year] = $in_assessment ?? [];
        $in_group_years = $in_group_year ?? [];
        $in_group_houses = $in_group_house ?? [];

        //$out_assessments[$round.'-'.$year] = $out_assessment ?? [];
        $out_group_years = $out_group_year ?? [];
        $out_group_houses = $out_group_house ?? [];
            
        return [ 
            'in_group_houses' => $in_group_houses, 
            'out_group_houses' => $out_group_houses,
            'in_group_years' => $in_group_years, 
            'out_group_years' => $out_group_years,
            'in_assessments' => collect($in_assessment)->groupBy('student_id'),
            'out_assessments' => collect($out_assessment)->groupBy('student_id'),
         ];
    }

    public function getPolarBias( $record, $count ) {
        $common_risk = $this->common_risk;
        if( (float)$record->P <= 3 && $common_risk == 'LOW_SELF_DISCLOSURE' ) $count++;
        if( (float)$record->P >= 12 && $common_risk == 'HIGH_SELF_DISCLOSURE' ) $count++;
        if( (float)$record->S <= 3 && $common_risk == 'LOW_TRUST_OF_SELF' ) $count++;
        if( (float)$record->S >= 12 && $common_risk == 'HIGH_TRUST_OF_SELF') $count++;
        if( (float)$record->L <= 3 && $common_risk == 'LOW_TRUST_OF_OTHERS' ) $count++;
        if( (float)$record->L >= 12 && $common_risk == 'HIGH_TRUST_OF_OTHERS' ) $count++;
        if( (float)$record->X <= 3 && $common_risk == 'LOW_SEEKING_CHANGE' ) $count++;
        if( (float)$record->X >= 12 && $common_risk == 'HIGH_SEEKING_CHANGE' ) $count++;
        return $count;
    }

    public function compositeBiasCount( $composite_risks, $count ) {
        $common_risk = $this->common_risk;
        if( $common_risk == 'OVER_REGULATION' && in_array('Over Regulation', $composite_risks) ) $count++;
        if( $common_risk == 'HIDDEN_VULNERABILITY' && in_array('Hidden Vulnerability', $composite_risks) ) $count++;
        if( $common_risk == 'HIDDEN_AUTONOMY' && in_array('Hidden Autonomy', $composite_risks) ) $count++;
        if( $common_risk == 'SOCIAL_NAIVETY' && in_array('Social Naivety', $composite_risks) ) $count++;
        if( $common_risk == 'SEEKING_CHANGE_INSTABILITY' && in_array('Seeking Change Instability', $composite_risks) ) $count++;
        return $count;
    }

    public function getCompositeBias( $record,$assessment_type, $second_data, $count ) {
        $rawdata = RawDataArray($record);
        $record = ModifyScoreData($record);
        $other_composite_risks = ( new Composite )->StudentCompositeRisksObject( $record, $rawdata, $assessment_type, []);
        if( $assessment_type == 'IN_SCHOOL') {
            $data_out_of_school = $second_data[$record->student_id][0] ?? null;
            if( $data_out_of_school ) {
                $data_out_of_school = (object)$data_out_of_school;
                $data_out_of_school = ModifyScoreData($data_out_of_school);
                $out_of_school_rawdata = RawDataArray($data_out_of_school);
                $sci_composite_risks = ( new Composite )->StudentSCICompositeRisksObject( $record, $data_out_of_school,$rawdata, $out_of_school_rawdata, [] );
            }
        }
        if( $assessment_type == 'OUT_OF_SCHOOL') {
            $data_in_school = $second_data[$record->student_id][0] ?? null;
            if( $data_in_school ) {
                $data_in_school = (object)$data_in_school;
                $data_in_school = ModifyScoreData($data_in_school);
                $in_school_rawdata = RawDataArray($data_in_school);
                $sci_composite_risks = ( new Composite )->StudentSCICompositeRisksObject( $data_in_school, $record, $in_school_rawdata, $rawdata, [] );
            }
        }

        $composite_risks = array_merge( $other_composite_risks['risks'] ?? [], $sci_composite_risks['risks'] ?? [] );
        $composite_risks = array_column($composite_risks, 'label');
        
        return $this->compositeBiasCount( $composite_risks, $count );
    }

    public function response($data, $value, $type, $risk_object, $prefix ) {
        $count = count($data);
        if( $type == 'IN_SCHOOL') $risk_count = $risk_object['in_count'] ?? 0;
        else $risk_count = $risk_object['out_count'] ?? 0;
        $risks = $risk_object['common_risk'];
        if( isset($risks[$value]) ) {
            if( $type == 'IN_SCHOOL') $risks[$value]['in_percentage'] = round(( $risk_count / ( $count == 0 ? 1 : $count ) ) * 100, 1);
            else if( $type == 'OUT_OF_SCHOOL') { 
                $risks[$value]['out_percentage'] = round(( $risk_count / ( $count == 0 ? 1 : $count ) ) * 100, 1);
                $risks[$value]['out_total'] = $count;
                $risks[$value]['out_risk_count'] = $risk_count;
            }
        }else {
            $risks[$value]['risk'] = $this->common_risk;
            $risks[$value]['name'] = $prefix.' '.$value;

            $risks[$value]['year'] = (int)$this->year;
            $risks[$value]['round'] = $this->round;
            if( $type == 'IN_SCHOOL') {
                $risks[$value]['in_percentage'] = round(( $risk_count / ( $count == 0 ? 1 : $count ) ) * 100, 1);
                $risks[$value]['in_total'] = $count;
                $risks[$value]['in_risk_count'] = $risk_count;
            }
            else if( $type == 'OUT_OF_SCHOOL') $risks[$value]['out_percentage'] = round(( $risk_count / ( $count == 0 ? 1 : $count ) ) * 100, 1);
        }
        return $risks;
    }

    public function getRiskBiases( $data, $value, $assessment_type, $second_data, $risk_object, $prefix ) {
        $risk_category = $this->riskCategory();
        $type_prefix = $assessment_type == "IN_SCHOOL" ? "in_" : "out_";
        $risk_object[$type_prefix.'count'] = 0;
        foreach( $data as $record) {
            $record = (object)$record;
            if( $risk_category == 'polar' ) $risk_object[$type_prefix.'count'] = $this->getPolarBias( $record, $risk_object[$type_prefix.'count'] );
            else if ( $risk_category == 'composite' ) $risk_object[$type_prefix.'count'] = $this->getCompositeBias( $record, $assessment_type, $second_data,  $risk_object[$type_prefix.'count'] );
        }
        $risk_object['common_risk'] = $this->response($data, $value, $assessment_type, $risk_object, $prefix );
        return $risk_object;
    }

    public function calculatePolarBias($data) {
        $houses = array_keys($data['in_group_houses'] ?? []);
        $years = array_keys($data['in_group_years'] ?? []);
        natsort( $houses );
        sort( $years );
        $out_houses = array_keys($data['out_group_houses'] ?? []);
        $out_years = array_keys($data['out_group_years'] ?? []);
        $house_risk_count = $year_risk_count = 0;
        $house_common_risk = [ 'common_risk' => null ];
        $year_common_risk = [ 'common_risk' => null ];
        foreach( $houses as $house )
            $house_common_risk = $this->getRiskBiases( $data['in_group_houses'][$house], $house, 'IN_SCHOOL', $data['out_assessments'], $house_common_risk, ""  );
        foreach( $years as $year ) 
            $year_common_risk = $this->getRiskBiases( $data['in_group_years'][$year], $year, 'IN_SCHOOL', $data['out_assessments'], $year_common_risk, "Year"  );
        foreach( $out_houses as $house ) 
            $house_common_risk = $this->getRiskBiases( $data['out_group_houses'][$house], $house, 'OUT_OF_SCHOOL', $data['in_assessments'], $house_common_risk, ""  );
        foreach( $out_years as $year ) 
            $year_common_risk = $this->getRiskBiases( $data['out_group_years'][$year], $year, 'OUT_OF_SCHOOL', $data['in_assessments'], $year_common_risk, "Year"  );
        
        return [ 'year' => array_values($year_common_risk['common_risk'] ?? []), 'house' => array_values($house_common_risk['common_risk'] ?? []) ];
    }

    public function groupComparism( $year, $round ) {
        $filter['round'][0] = $this->round;
        $filter['academic_year'][0] = $this->year;
        $filter['campus'] = request()->get('campus');
        $data = $this->fetchLastAssessments( $filter );
        return $this->calculatePolarBias($data);
    }

    public function countRisk( $school_data) {
        $builder = [];
        foreach( $school_data as $data ) {
            if( $data->P <= 3 )
                $builder[] = 'LOW_SELF_DISCLOSURE';
            if( $data->P >= 12 )
                $builder[] = 'HIGH_SELF_DISCLOSURE';
            if( $data->S <= 3 )
                $builder[] = 'LOW_TRUST_OF_SELF';
            if( $data->S >= 12 )
                $builder[] = 'HIGH_TRUST_OF_SELF';
            if( $data->L <= 3 )
                $builder[] = 'LOW_TRUST_OF_OTHERS';
            if( $data->L >= 12 )
                $builder[] = 'HIGH_TRUST_OF_OTHERS';
            if( $data->X <= 3 )
                $builder[] = 'LOW_SEEKING_CHANGE';
            if( $data->X >= 12 )
                $builder[] = 'HIGH_SEEKING_CHANGE';
        }
        $values = array_count_values($builder);
        arsort($values);
        $values = array_keys($values);
        return $values[0] ?? null;
    }


    public function getSchoolSpecificRisk( $request, $school_id ) {
        try {
            $filter['academic_year'][0] = $request->get('academic_year') ?? $this->datSchool->SchoolAcademicYear( $school_id );
            $filter['round'][0] = $request->get('assessment_round');
            $filter['campus'] = $request->get('campus');
            //$school_population = $this->pop->studentWithData( $year );
            //IN_SCHOOL
            $in_school_data = $this->assMain->getStudentCount( $filter, [ 'hs', 'sch' ], 'viewall', $request->get('assessment_round') ?? 1 );
            $in_risk_count = $this->countRisk( $in_school_data );
            $total_assessment = count( $in_school_data );

            //OUT_OF_SCHOOL
            $out_school_data = $this->assMain->getStudentCount( $filter, [ 'at' ], 'viewall', $request->get('assessment_round') ?? 1 );
            $out_risk_count = $this->countRisk( $out_school_data );
            return [ 'in_common_risk' => $in_risk_count, 'out_common_risk' => $out_risk_count ];
            
        }catch( \Illuminate\Database\QueryException $ex ) {
        }
      
    }

    public function commonRisk($request, $school_id) {
        $year = $request->get('academic_year') ?? $this->datSchool->SchoolAcademicYear( $school_id );
        $campus_year_round = $this->round->getAllCampusInProgressRound( $school_id, $year, [] );
        $current_round = $request->get('assessment_round') ?? collect($campus_year_round)->max('round') ?? 1;
        $this->round = $current_round;
        $this->year = $year;
        $risk = $this->getSchoolSpecificRisk( $request, $school_id );
        if( !isset($risk['in_common_risk']) ) return (object)[];
        $this->common_risk = $risk['in_common_risk'];
        $common_risk_information = $this->getRiskInformation( $this->common_risk );
        if( $this->common_risk ) $common_risk_information->comparism = $this->groupComparism( $year, $current_round, $this->common_risk  );
        return $common_risk_information;
    }

    public function getStudentRisk( $risk ) {
        $risk_abber = GetBiasInAbbrevAP($risk);
        $depend_value = ( $risk == 'POLAR_LOW_TRUST_OF_SELF' || $risk == 'POLAR_HIGH_TRUST_OF_SELF' || 
                          $risk == 'POLAR_LOW_TRUST_OF_OTHERS' || $risk == 'POLAR_HIGH_TRUST_OF_OTHERS') ? 7.5 : null;
        $data = ( new Model_student_tracking_risks )->risks($risk_abber, $depend_value);
        return $data->groupBy('category');
    }

    public function getRiskInformation( $common_risk ) {
        $info = $this->commonRisk->getESRRisk( 'POLAR_'.$common_risk.'' );
        if( !isset( $info->description ) ) 
            return null;
        
        $risk = 'POLAR_'.$common_risk.'';
        $data = $this->getStudentRisk( $risk );
        $risk = str_replace( '_', ' ', ( strtolower( $risk ) ) );
        $info->common_risk['description'] = $info->description ?? null;
        $info->common_risk['name'] =  ucwords( $risk );
        $info->learning_risks = collect($data['LEARNING_RISK'])->pluck('question') ?? [];
        $info->emotional_risks = collect($data['MENTAL_HEALTH_RISK'])->pluck('question') ?? [];
        $info->social_risks = collect($data['SOCIAL_EMOTIONAL_RISK'])->pluck('question') ?? []; 
        if( $info ) {
            unset( $info->name);
            unset( $info->description);
        }
       
        return $info;
    }



}