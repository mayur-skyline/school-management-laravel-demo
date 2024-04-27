<?php
namespace App\Services;
use App\Models\Dbschools\Model_multischools;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbglobal\Model_groupdash;
use App\Models\Dbglobal\Model_groupRisk;
use App\Util\Grouping\RoundManagement\Round;
use App\Util\Grouping\CommonRisk\Risk;
use App\Models\Dbglobal\Model_impact_meter;
use DB;

class AstNextGroupDashboardAnalyticsServiceProvider
{
    public function __construct()
    {
        $this->multiSchool = new Model_multischools;
        $this->groupdash = new Model_groupdash();
        $this->datSchool = new Model_dat_schools();
        $this->groupRisk = new Model_groupRisk();
        $this->round = new Round();
        $this->commonRisk = new Risk();
    }

    public function userSchool() {
        $user_id = auth()->user()->id;
        return $this->multiSchool->userSchool( $user_id );
    }

    public function getAnalytics( $groupname ) {
        return $this->groupdash->getAnalytics( $groupname );
    }

    public function getCommomRiskAnalytics( $groupname ) {
        return $this->groupRisk->getAnalytics( $groupname );
    }

    public function groupDataByYearAndRound( $rawdata ) {
        $groupdata = [];
        $allgroupdata = [];
        $copydata = $rawdata;
        $data = [];
        foreach( $copydata as $value ) {
            $allgroupdata[ $value->round.'-'.$value->year ][$value->assessment_type][] = $value;
            if( !isset( $processed[ $value->round.'-'.$value->year ]['percentage'] ) ) {
                $result = $rawdata->where( 'year', $value->year )->where( 'round', $value->round )
                                   ->where( 'assessment_type', 'IN_SCHOOL' );
                $population = $result->pluck('school_population')->sum();
                $total_assessment_taken = $result->pluck('total_student_analysed')->sum();
                $percent = $total_assessment_taken / ( $population == 0 ? 1 : $population );
                $processed[ $value->round.'-'.$value->year ]['percentage'] = $percent;
                $value->percent = $percent;
                if( $percent >= 0.1 ) {
                    $groupdata[ $value->round.'-'.$value->year ][$value->assessment_type][] = $value;
                    $data[] = $value;
                }
            }
            else if( $processed[ $value->round.'-'.$value->year ]['percentage'] >= 0.1 ) {
                $groupdata[ $value->round.'-'.$value->year ][$value->assessment_type][] = $value;
                $value->percent = $percent;
                $data[] = $value;
            }
            
        }

        return [ 'data' => $data, 'groupdata' => $groupdata, 'allgroupdata' => $allgroupdata ];
    }

    public function generalRiskProcess() {
        $school_data_attached = $this->userSchool();
        $rawdata = $this->getCommomRiskAnalytics( $school_data_attached->schools );
        [ 'data' => $data, 'groupdata' => $groupdata,  'allgroupdata' => $allgroupdata ] = $this->groupDataByYearAndRound( $rawdata );
        return [ 'groupdata' => $groupdata, 'data' => $data, 'rawdata' => $rawdata,  'allgroupdata' => $allgroupdata ];
    }

    public function generalProcess() {
        $school_data_attached = $this->userSchool();
        $rawdata = $this->getAnalytics( $school_data_attached->schools );
        [ 'data' => $data, 'groupdata' => $groupdata,  'allgroupdata' => $allgroupdata ] = $this->groupDataByYearAndRound( $rawdata );
        return [ 'groupdata' => $groupdata, 'data' => $data, 'rawdata' => $rawdata,  'allgroupdata' => $allgroupdata ];
    }

    public function separateIntoVariant( $data ) {
        $in_school = array_filter($data, function($key) use ($data ) {
            return $data[$key]['assessment_type'] == 'IN_SCHOOL';
        }, ARRAY_FILTER_USE_KEY);
        $in_school = array_values( $in_school );
        $out_of_school = array_filter($data, function($key) use ($data ) {
            return $data[$key]['assessment_type'] == 'OUT_OF_SCHOOL';
        }, ARRAY_FILTER_USE_KEY);
        $out_of_school = array_values( $out_of_school );
        return [ 'in_school' => $in_school, 'out_of_school' => $out_of_school ];
    }


    public function getCurrentAndPreviousData( $school_data, $name ) {
        $in_current_data = $in_previous_data = $out_current_data = $out_previous_data = null;
        $school = $school_data;
        $school_data['in_school'] = getDatathatMeettheTenPercentMark( $school_data['in_school'] );
        $school_data['out_of_school'] = getDatathatMeettheTenPercentMark( $school_data['out_of_school'] );
        if( isset($school_data['in_school'][ count( $school_data['in_school'] ) - 1 ]) ) {
            $value = $school_data['in_school'][ count( $school_data['in_school'] ) - 1 ];
            $in_current_data = $value;
            $value[ 'total_student_analysed' ] = $value[ 'total_student_analysed' ] == 0 ? 1 : $value[ 'total_student_analysed' ];
            $in_current_data['priority_percentage'] = ( $value['student_count'] / $value[ 'total_student_analysed' ] ) * 100;
        }
        if( isset($school_data['in_school'][ count( $school_data['in_school'] ) - 2 ]) ) {
            $value = $school_data['in_school'][ count( $school_data['in_school'] ) - 2 ];
            $in_previous_data = $value;
            $value[ 'total_student_analysed' ] = $value[ 'total_student_analysed' ] == 0 ? 1 : $value[ 'total_student_analysed' ];
            $in_previous_data['priority_percentage'] = ( $value['student_count'] / $value[ 'total_student_analysed' ] ) * 100;
        }
        if( isset($school_data['out_of_school'][ count( $school_data['out_of_school'] ) - 1 ]) ) {
            $value = $school_data['out_of_school'][ count( $school_data['out_of_school'] ) - 1 ];
            $out_current_data = $value;
            $value[ 'total_student_analysed' ] = $value[ 'total_student_analysed' ] == 0 ? 1 : $value[ 'total_student_analysed' ];
            $out_current_data['priority_percentage'] = ( $value['student_count'] / $value[ 'total_student_analysed' ] ) * 100;
        }
        if( isset($school_data['out_of_school'][ count( $school_data['out_of_school'] ) - 2 ]) ) {
            $value = $school_data['out_of_school'][ count( $school_data['out_of_school'] ) - 2 ];
            $out_previous_data = $value;
            $value[ 'total_student_analysed' ] = $value[ 'total_student_analysed' ] == 0 ? 1 : $value[ 'total_student_analysed' ];
            $out_previous_data['priority_percentage'] = ( $value['student_count'] / $value[ 'total_student_analysed' ] ) * 100;
        }
        $current_data =  ( isset( $out_current_data['priority_percentage'] ) ? $out_current_data['priority_percentage']  : 0 ) - ( isset( $in_current_data['priority_percentage'] ) ? $in_current_data['priority_percentage'] : 0 );
        $previous_data =  ( isset( $out_previous_data['priority_percentage'] ) ? $out_previous_data['priority_percentage']  : 0 ) - ( isset( $in_previous_data['priority_percentage'] ) ? $in_previous_data['priority_percentage'] : 0 );
        
        if( !isset( $in_previous_data['priority_percentage']) || !isset( $out_previous_data['priority_percentage'])  )
            $change = 'nil';

        $change = (float)number_format( ( $current_data - $previous_data ), 1 );
        
        if( !$in_current_data )
            return null;

        return [
                'name' => $name,
                'impact' => (float)number_format( $current_data, 1 ),
                'change' => $change,
                'round' => $in_current_data['round'] ?? null,
                'year' => $in_current_data['year'] ?? null,
                'school_data' => $school_data,
                'school' => $school
            ];
        
    } 

    public function schoolComparism( $data ) {
        $builder = [];
        $data_arr = json_decode(json_encode($data), true);
        $names = array_column( $data_arr, 'name' );
        $names = array_values( array_unique( $names ) );
        foreach( $names as $name ) {
            $result = array_filter($data_arr, function($key) use ($data_arr, $name ) {
                        return $data_arr[$key]['name'] == $name;
                    }, ARRAY_FILTER_USE_KEY);
            $result = array_values( $result );
            $school_data = $this->separateIntoVariant( $result );
            $responses = $this->getCurrentAndPreviousData( $school_data, $name );
            if( $responses )
                $builder[] = $responses;
        }
        $max = $this->getMaxRoundYear( $builder );
        $builder = $this->sortIsHistoricData( $builder, $max );
        return sortResult($builder, 'impact');
    }

    public function getMaxRoundYear( $data ) {
       
        $years = array_column($data, 'year');
        $rounds = array_column($data, 'round');
        $max_year = count( $years ) > 0 ? max( $years ) : null;
        $temp = [];
        foreach( $years as $key => $year ) {
            if( $year == $max_year )
                $temp[] = $rounds[$key];
        }
        $max_round = count( $temp ) > 0 ? max( $temp ) : null;
        return [
            'year' => $max_year,
            'round' => $max_round
        ];
    }

    public function sortIsHistoricData( $builder, $max ) {
        $school_comparism = $builder;
        if( $max['year'] == null ) 
            return $builder;
        
        foreach( $school_comparism as $key => $data ) {
            if( $max['year'] > $data['year'] )
                $builder[$key]['is_historic'] = true;
            else if( $max['year'] == $data['year'] && $max['round'] > $data['round'] )
                $builder[$key]['is_historic'] = true;
            else
                $builder[$key]['is_historic'] = false;
        }
        return $builder;
    }

    public function changeFn( $trends ) {
        $most_recent_data = $trends[ count( $trends) - 1 ] ?? null;
        $immediate_past_data = $trends[ count( $trends ) - 2 ] ?? null;
        if( $most_recent_data && $immediate_past_data )
            return (float)number_format( ( $most_recent_data['percentage'] - $immediate_past_data['percentage'] ), 1 );
        if( $most_recent_data && !$immediate_past_data )
            return 'nil';
        return null;
    }

    public function limitTrendSize( $trends ) {
        $trends = array_reverse( $trends );
        $trends = array_slice( $trends, 0, 5 );
        return array_reverse( $trends );
    }

    public function impactMeter( $impact, $change ) {
        if( $impact >= 0 ) $position = "Higher than 0";
        else if ( $impact >= -5 && $impact < 0  ) $position = "Between -5 and 0";
        else if ( $impact >= -10 && $impact < -5  ) $position = "Between -10 and -5";
        else if( $impact >= -15 && $impact < -10)   $position = "Between -15 and -10";
        else $position = "Between -15 and -10";
        $impact_info = ( new Model_impact_meter )->getImpactMeter( $position ); 
        $impact_meter = [ 'impact' => round($impact, 1), 'change' => round( $change, 1), 'title' => $impact_info->title, 'text' => $impact_info->text ];
        return $impact_meter;
    }

    public function sumData( $group_key, $groupdata, $data ) {
        $trends = [];
        foreach( $group_key as $key ) {
            $assessment_type_key = array_keys( $groupdata[ $key ] );
            $trends[] = $this->sumByType( $assessment_type_key, $groupdata[ $key ] );
        }
        $trends = $this->limitTrendSize( $trends );
        $school_comparism = $this->schoolComparism( $data );
        $priority_diff = $trends[ count( $trends ) - 1 ]['percentage'] ?? 0;
        $change = $this->changeFn( $trends );
        $meter = $this->impactMeter( $priority_diff ?? null, $change ?? 0 );
        return [ 'change' => $change, 'priority_percentage' => $priority_diff, 'trends' => $trends, 'school_comparism' => $school_comparism, 'impact_meter' => $meter ];
    }

    public function sumByType( $assessment_type_key, $assessment_data ) {
        $in_school_priority = 0;
        $out_of_school_priority = 0;
        foreach( $assessment_type_key as $key ) {
            $student_count =  array_sum( array_column( $assessment_data[$key], 'student_count' ) );
            
            $total_student_analysed = array_sum( array_column( $assessment_data[$key], 'total_student_analysed' ) );
            $total_student_analysed = $total_student_analysed == 0 ? 1 : $total_student_analysed;
            
            $round = $assessment_data[$key][0]->round;
            $year = $assessment_data[$key][0]->year;
            if( $key == 'IN_SCHOOL' )
                $in_school_priority = ( $student_count / $total_student_analysed ) * 100;
            if( $key == 'OUT_OF_SCHOOL' )
                $out_of_school_priority = ( $student_count / $total_student_analysed ) * 100;

        }
        $diff_priority = $out_of_school_priority - $in_school_priority;
        return [ "round" => $round, "year" => $year, "label" => "A$round($year)", "percentage" => (float)number_format( $diff_priority, 1 ) ];
    }

    public function emotionalWellbeing() {
        [ 'groupdata' => $groupdata, 'data' => $data ] = $this->generalProcess();
        $group_key = array_keys( $groupdata );
        return $this->sumData( $group_key, $groupdata, $data );
    }


    public function schoolDataByType( $assessment_type_key, $assessment_data ) {
        $in_school_priority = 0;
        $out_of_school_priority = 0;
        foreach( $assessment_type_key as $key ) {
            $student_count =  array_sum( array_column( $assessment_data[$key], 'student_count' ) );
            $total_student_analysed = array_sum( array_column( $assessment_data[$key], 'total_student_analysed' ) );
            $total_student_analysed = $total_student_analysed == 0 ? 1 : $total_student_analysed;
            
            $round = $assessment_data[$key][0]->round;
            $year = $assessment_data[$key][0]->year;
            if( $key == 'IN_SCHOOL' )
                $in_school_priority = ( $student_count / $total_student_analysed ) * 100;
            if( $key == 'OUT_OF_SCHOOL' )
                $out_of_school_priority = ( $student_count / $total_student_analysed ) * 100;
        }
        return [ 
            'IN_SCHOOL' => [ "round" => $round, "year" => $year, "label" => "A$round($year)", "percentage" => (float)number_format( $in_school_priority, 1 )   ],
            'OUT_OF_SCHOOL' => [ "round" => $round, "year" => $year, "label" => "A$round($year)", "percentage" => (float)number_format( $out_of_school_priority, 1 ) ]
        ];
    }

    public function studentRiskSchoolComparism( $sorted_data, $names ) {
        $data = [];
        foreach( $names as $name ){
            $in_percentage = $out_percentage = null;
            $data_before_filter = $sorted_data[$name];
            if( isset( $sorted_data[ $name ][ 'IN_SCHOOL' ] ) )
                $sorted_data[ $name ][ 'IN_SCHOOL' ] = getDatathatMeettheTenPercentMark( $sorted_data[ $name ][ 'IN_SCHOOL' ] );
            if( isset($sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] ) )
                $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] = getDatathatMeettheTenPercentMark( $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] );

            if( isset( $sorted_data[ $name ][ 'IN_SCHOOL' ] ) &&
                isset( $sorted_data[ $name ][ 'IN_SCHOOL' ][ count( $sorted_data[ $name ][ 'IN_SCHOOL' ] ) - 1 ] ) ) {
                $size = count( $sorted_data[ $name ][ 'IN_SCHOOL' ] );
                $in_school_comparism = $sorted_data[ $name ][ 'IN_SCHOOL' ][ $size - 1 ];
                $priority = $in_school_comparism['student_count'];
                $total_student_analysed = $in_school_comparism['total_student_analysed'];
                $total_student_analysed = $total_student_analysed == 0 ? 1 : $total_student_analysed;
                $in_percentage = ( $priority / $total_student_analysed ) * 100;
            }

            if( isset($sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] ) && 
                isset($sorted_data[ $name ][ 'OUT_OF_SCHOOL' ][ count( $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] ) - 1 ]) ) {
                $size = count( $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] );
                $out_school_comparism = $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ][ $size - 1 ];
                $priority = $out_school_comparism['student_count'];
                $total_student_analysed = $out_school_comparism['total_student_analysed'];
                $total_student_analysed = $total_student_analysed == 0 ? 1 : $total_student_analysed;
                $out_percentage = ( $priority / $total_student_analysed ) * 100;
            }  

            if( (  isset( $sorted_data[ $name ][ 'IN_SCHOOL' ] ) &&
                   count( $sorted_data[ $name ][ 'IN_SCHOOL' ] ) > 0 ) || 
                (  isset( $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] ) &&
                   count( $sorted_data[ $name ][ 'OUT_OF_SCHOOL' ] ) > 0 ) 
              ) {
                $data[] = [ 
                    'name' => $name, 
                    'in_school_percentage' => (float)number_format( $in_percentage, 1 ), 
                    'out_school_percentage' => (float)number_format( $out_percentage, 1 ),
                    'round' => $in_school_comparism['round'] ?? null,
                    'year' => $in_school_comparism['year'] ?? null,
                    'data_before_filter' => $data_before_filter,
                    'data_after_filter' => $sorted_data[$name]
                ];
            }
            
        }
       
        $max = $this->getMaxRoundYear( $data );
        $data = $this->sortIsHistoricData( $data, $max );
        $data = sortResult( $data, 'in_school_percentage');
        return $data;
    } 

    public function sortBySchoolName( $data ) {
        $data_arr = json_decode( json_encode( $data ));
        $names = array_values( array_unique( ( array_column( $data_arr, 'name' ) ) ) );
        $sorted_data = [];
        foreach( $data as $value ) {
             $sorted_data[ $value->name ][ $value->assessment_type ][] = $value;
        }
        return [ 'sorted_data' => $sorted_data, 'school_names' => $names ];
    } 

    public function studentRiskAnalytics( $group_key, $groupdata, $data  ) {
        $IN_SCHOOL['trends'] = $OUT_OF_SCHOOL['trends'] = [];
        foreach( $group_key as $key ) {
            $assessment_type_key = array_keys( $groupdata[ $key ] );
            $priority = $this->schoolDataByType( $assessment_type_key, $groupdata[ $key ] );
            $IN_SCHOOL['trends'][] = $priority['IN_SCHOOL'];
            $OUT_OF_SCHOOL['trends'][] = $priority['OUT_OF_SCHOOL'];
        }
        $IN_SCHOOL['trends'] = $this->limitTrendSize( $IN_SCHOOL['trends'] );
        $OUT_OF_SCHOOL['trends'] = $this->limitTrendSize( $OUT_OF_SCHOOL['trends'] );
        $in_size = count( $IN_SCHOOL['trends'] );
        $out_size = count( $OUT_OF_SCHOOL['trends'] );
        $IN_SCHOOL['student_risk'] = isset( $IN_SCHOOL['trends'][ $in_size - 1 ]['percentage'] ) ? $IN_SCHOOL['trends'][ $in_size - 1 ]['percentage'] : 0;
        if( IsTrackingPlatformGroup( auth()->user() ) ) {
            $OUT_OF_SCHOOL['student_risk'] = '-';
        }else {
            $OUT_OF_SCHOOL['student_risk'] = isset( $OUT_OF_SCHOOL['trends'][ $out_size - 1 ]['percentage'] ) ? $OUT_OF_SCHOOL['trends'][ $out_size - 1 ]['percentage'] : 0;
        }
        return [ 'IN_SCHOOL' => $IN_SCHOOL, 'OUT_OF_SCHOOL' => count( $OUT_OF_SCHOOL['trends'] ) > 0 ? $OUT_OF_SCHOOL : null ];
    }

    public function studentRisk() {
        [ 'groupdata' => $groupdata, 'data' => $data ] = $this->generalProcess();
        $group_key = array_keys( $groupdata );
        $result = $this->studentRiskAnalytics( $group_key, $groupdata, $data );
        [ 'sorted_data' => $sorted_data, 'school_names' => $names ] = $this->sortBySchoolName( $data );
        $result['school_comparism'] = $this->studentRiskSchoolComparism( $sorted_data, $names );
        return $result;
    }

    public function groupDataByRisk( $data ) {
        $result = [];
        foreach( $data as $value ) {
            $result[ $value->risk ][] = $value;
        }

        return $result;
    }

    public function AddACYear( $data ) {
        foreach( $data as $key => $value ) {
            $year_start = $value->year_start;
            $academic_year = date('Y');
            if ($year_start == 1) 
                $year_start = $year_start;
            else 
                $year_start = $year_start - 1;
            if ($year_start == "") 
                $year_start = 9;
            if ( date("n") < $year_start ) 
                $academic_year--;
            $data[$key]->acyear = $academic_year;
        }
        
        return $data;
    }

    public function fetchCurrentRoundOnly( $curr_year_data, $data ) {
        $result = [];
        foreach( $curr_year_data as $value ) {
            foreach( $data as $d ) {
                if( $value->round == $d->round && $value->acyear == $d->year && $value->id == $d->school_id ) {
                    $result[] = $d;
                }
            }
        }
        return $result;
    }

    public function getCurrentRound( $data ) {
        $result = [];
        $data_arr = json_decode( json_encode( $data ) );
        $school_ids = array_values ( array_unique( array_column( $data_arr, 'school_id' ) ) );
        $groupdata = $this->datSchool->getSchoolInAGroup( $school_ids );
        $groupdata = $this->AddACYear( $groupdata );
        foreach( $groupdata as $key => $value ) {
            $groupdata[$key]->round = $this->round->getInProgressRound( $value->id, $value->acyear, [] );
        }

        return $groupdata;
    }

    public function getLowestRiskCount( $groupdata ) {
        $group_keys = array_keys( $groupdata );
        $min_risk = [];
        foreach( $group_keys as $key ) {
            $risk = $groupdata[ $key ];
            $count_list = array_column( $risk, 'count' );
            $minimum = min( $count_list );
            $min_risk[] = [ 'risk' => $key, 'common' => $minimum ];
        }
        return $min_risk;
    }

    public function getHighestRiskCountAmongLowest( $lowest_risk_count ) {
        $common = array_column( $lowest_risk_count, 'common' );
        $max = max( $common ) ?? null;
        if( $max ) {
            $index = array_search( $max, $common );
            return $lowest_risk_count[$index];
        }

        return null;
        
    }

    public function getRiskInformation( $common_risk ) {
        $info = $this->commonRisk->getRisk( 'POLAR_'.$common_risk.'' );
        if( !isset( $info->description ) ) 
            return null;
        
        $risk = 'POLAR_'.$common_risk.'';
        $risk = str_replace( '_', ' ', ( strtolower( $risk ) ) );
        $info->common_risk['description'] = $info->description ?? null;
        $info->common_risk['name'] =  ucwords( $risk );
        if( $info ) {
            unset( $info->name);
            unset( $info->description);
        }
       
        return $info;
    }

    public function commonRisk() {
        [ 'groupdata' => $groupdata, 'data' => $data ] = $this->generalRiskProcess();
        $data = collect( $data );
        $max = $data->max( 'polar_bias_percentage' );
        $result = $data->where( 'polar_bias_percentage', $max )->all();
        $result = array_values( $result );
        $common_risk = $result[0]->risk ?? null;
        return $this->getRiskInformation( $common_risk );
        
    }



}