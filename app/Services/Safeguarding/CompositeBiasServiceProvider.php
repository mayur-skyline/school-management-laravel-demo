<?php
namespace App\Services\Safeguarding;
use App\Models\Dbglobal\Model_dat_schools;
use App\Util\Grouping\RoundManagement\Round;
use App\Models\Dbschools\Model_ass_main;
use App\Services\CohortDataFilterServiceProvider;
use App\Util\Grouping\Composite\Composite;
use DB;

class CompositeBiasServiceProvider
{
    public function __construct()
    {
        $this->datSchool = new Model_dat_schools();
    }


    public function groupByRisks( $school_data_in_school, $school_data_out_of_school ) {
       $roundYears = array_keys($school_data_in_school);
       foreach ($roundYears as $index => $value) {
            foreach ($school_data_in_school[$value] as $key => $record) {
                $school_data_in_school[$value][$key]->has_overRegulation = 0;
                $school_data_in_school[$value][$key]->has_sci = 0;
                if( in_array( 'Over Regulation', $record->compositeRisks) )
                    $school_data_in_school[$value][$key]->has_overRegulation = 1;
                if( in_array( 'Seeking Change Instability', $record->compositeRisks) )
                    $school_data_in_school[$value][$key]->has_sci = 1;
            }
       }
       $roundYears = array_keys($school_data_out_of_school);
       foreach ($roundYears as $index => $value) {
            foreach ($school_data_out_of_school[$value] as $key => $record) {
                $school_data_out_of_school[$value][$key]->has_overRegulation = 0;
                $school_data_out_of_school[$value][$key]->has_sci = 0;
                if( in_array( 'Over Regulation', $record->compositeRisks) )
                    $school_data_out_of_school[$value][$key]->has_overRegulation = 1;
                if( in_array( 'Seeking Change Instability', $record->compositeRisks) )
                    $school_data_out_of_school[$value][$key]->has_sci = 1;
            }
        }
        return [ 'school_data_in_school' => $school_data_in_school, 'school_data_out_of_school' => $school_data_out_of_school ];
    }
}
