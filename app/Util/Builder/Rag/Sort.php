<?php

namespace App\Util\DBSort;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class Sort
{

    public function SortByAtoZ( Builder $query, ?string $sort_by ) {
        if ($sort_by == '' || empty($sort_by)) {
            $query = $query->orderBy('population.firstname', 'asc');
        }
        return $query;
    }

    public function SortByParam( Builder $query, string $year, string $round, string $sort_by, 
                            string $sort_variant, string $order, string $page, string $size ): Builder
    {
       
        if( $sort_by == '' )
            return $query;
        if( $sort_variant == '' )
            return $query;

       
        list( 'last_round' => $last_round, 'last_year' => $last_year ) = $this->RoundAYGroup( $round, $year );
        if ($sort_by == 'priority_students') {
            // $query = $query->addSelect(  DB::raw( "CASE
            //     WHEN ( ( ass_score.P <= 3 || ass_score.P  >= 12 ) && 
            //           ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
            //           ( ass_score.S <= 3 || ass_score.S  >= 12 ) && 
            //           ( ( ass_score.P <= 3 || ass_score.P >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
            //           ( ass_score.L <= 3 || ass_score.L  >= 12 ) && 
            //           ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.P <= 3 || ass_score.P >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
            //           ( ass_score.X <= 3 || ass_score.X  >= 12 ) && 
            //           ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.P <= 3 || ass_score.P >= 12 )  )

            //         )
            //     THEN 1 ELSE 0
            //         END AS P1"
            // ) );
            // $query = $query->orderBy('P1', 'DESC');
            $query = $query->orderBy('ass_main_' . $year . '.priority_count', 'DESC');
        }
        if ($sort_by == 'composite_risks') {
            $query = $query->addSelect(  DB::raw( 'IFNULL(ass_main_' . $year . '.in_school_composite_count, 0) + IFNULL(ass_main_' . $year . '.out_of_school_composite_count, 0) AS comp_count' ) ); 
            $query = $query->orderBy('comp_count', 'DESC');
        }
        if ($sort_by == 'number_of_polar_biases' && $sort_variant == 'IN_SCHOOL') {
            $query = $query->orderByRaw('ass_main_' . $year . '.in_school_polar_count DESC');
        }
        if ($sort_by == 'number_of_polar_biases' && $sort_variant == 'OUT_OF_SCHOOL') {
            $query = $query->orderByRaw('ass_main_' . $year . '.out_of_school_polar_count DESC');
        }
        // if ($sort_by == 'increase_in_polar_biases' || $sort_by == 'decrease_in_polar_biases') {
        //     $query = $query->join('ass_main_' . $last_year . ' as last_ass_main', function ($join) use ($year, $last_round) {
        //         $q = $join->on('last_ass_main.pupil_id', '=', 'ass_main_' . $year . '.pupil_id')
        //             ->where('last_ass_main.is_completed', 'Y')
        //             ->whereIn('last_ass_main.round', $last_round);
        //     });
        //     if ($sort_by == 'increase_in_polar_biases' && $sort_variant == 'IN_SCHOOL') {
        //         $query = $query->orderByDesc(DB::raw('CASE WHEN((ass_main_' . $year . '.in_school_polar_count - last_ass_main.in_school_polar_count) = 0) THEN 0 WHEN((ass_main_' . $year . '.in_school_polar_count - last_ass_main.in_school_polar_count) > 0) THEN 1 ELSE -1 END'));
        //     }
        //     if ($sort_by == 'increase_in_polar_biases' && $sort_variant == 'OUT_OF_SCHOOL') {
        //         $query = $query->orderByDesc(DB::raw('CASE WHEN((ass_main_' . $year . '.out_of_school_polar_count - last_ass_main.out_of_school_polar_count) = 0) THEN 0 WHEN((ass_main_' . $year . '.out_of_school_polar_count - last_ass_main.out_of_school_polar_count) > 0) THEN 1 ELSE -1 END'));
        //     }
        //     if ($sort_by == 'decrease_in_polar_biases' && $sort_variant == 'IN_SCHOOL') {
        //         $query = $query->orderByDesc(DB::raw('CASE WHEN((last_ass_main.in_school_polar_count - ass_main_' . $year . '.in_school_polar_count) = 0) THEN 0 WHEN((last_ass_main.in_school_polar_count - ass_main_' . $year . '.in_school_polar_count) > 0) THEN 1 ELSE -1 END'));
        //     }
        //     if ($sort_by == 'decrease_in_polar_biases' && $sort_variant == 'OUT_OF_SCHOOL') {
        //         $query = $query->orderByDesc(DB::raw('CASE WHEN((last_ass_main.out_of_school_polar_count - ass_main_' . $year . '.out_of_school_polar_count) = 0) THEN 0 WHEN((last_ass_main.out_of_school_polar_count - ass_main_' . $year . '.out_of_school_polar_count) > 0) THEN 1 ELSE -1 END'));
        //     }
        // }
        return $query;
    }

    public function SortByFactorBias( Builder $query, string $sort_by, string $page, string $size, string $order ): Builder {
        if ($sort_by == 'self_disclosure' ) {
            $query = $query->addSelect( DB::raw( "ass_score.P * 1 as PF" ) );
            return $this->orderBy( $query, "PF", $order );
        }
        if( $sort_by == 'trust_of_self' ) {
            $query = $query->addSelect( DB::raw( "ass_score.S * 1 as SF" ) );
            return $this->orderBy( $query, "SF", $order );
        } 
        if( $sort_by == 'trust_of_others' ) {
            $query = $query->addSelect( DB::raw( "ass_score.L * 1 as LF" ) );
            return $this->orderBy( $query, "LF", $order );
        } 
        if( $sort_by == 'seeking_change' ) {
            $query = $query->addSelect( DB::raw( "ass_score.X * 1 as XF" ) );
            return $this->orderBy( $query, "XF", $order );
        } 

        return $query;
    }

    public function orderBy(Builder $query, $param, $order ) {
        if( $order == 'low' )
            return $query->orderBy($param, 'asc');
        else
            return $query->orderBy($param, 'desc');
    }

    public function RoundAYGroup( $round, $year ) {
        $last_round = ($round - 1 == 0) ? 3 : ($round - 1);
        $last_year = $last_round == 3 && $round != 3 ? ($year - 1) : $year;
        if( $last_round == 3 ) 
            return [ 'last_round' => 3, 'last_year' => $last_year ]; //[ 1, 2, 3 ]
        else if( $last_round == 2 )
            return [ 'last_round' => 2, 'last_year' => $last_year ]; //[ 1, 2 ]
        else
            return [ 'last_round' => 1, 'last_year' => $last_year ]; //[ 1 ]
    }


}
