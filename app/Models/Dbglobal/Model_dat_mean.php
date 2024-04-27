<?php

namespace App\Models\Dbglobal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_dat_mean extends Model {

    protected $table = "dat_mean";
    protected $year = null;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    public function setYear($year) {
        $this->year = $year; // Set protected year value to passed year
        if ($year != null) {
            $this->table = $this->getTable() . '_' . $year; // Set table name to arr_year_$year
        }
    }

    public static function year($year) { // create instance for dynamic year value
        $instance = new static;
        $instance->setYear($year);
        return $instance->newQuery();
    }

    public function getMean($data) {
        $year = $data['accyear'];
        $yr_selection = array();
        if(isset($data["syrs"]) && !empty($data["syrs"])){
            $yr_selection = $data['syrs'];
        }
        if (isset($data['gender_' . $year]) & !empty($data['gender_' . $year])) {
            $gender = $data['gender_' . $year];
        } else {
            $gender = '';
        }
        $schoolId = mySchoolId();
        $data = Model_dat_mean::year($year);
        if (!empty($yr_selection)) {
            $data->whereIn('yr_selection', $yr_selection);
        }
        if (isset($gender) && !empty($gender)) {
            $data->whereIn('gender', $gender);
        }
        $data->where('school_id', $schoolId);
        $data->orderBy('id', 'DESC');
        $data = $data->get();
        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }
   
    public function getUkMean($data) {
        $year = $data['accyear'];
        $yr_selection = array();
        if(isset($data["syrs"]) && !empty($data["syrs"])){
            $yr_selection = $data['syrs'];
        }
        if (isset($data['gender_' . $year]) & !empty($data['gender_' . $year])) {
            $gender = $data['gender_' . $year];
        } else {
            $gender = '';
        }
        $schoolId = mySchoolId();

        $data = Model_dat_mean::year($year);
        if (!empty($yr_selection)) {
            $data->whereIn('yr_selection', $yr_selection);
        }
        if (isset($gender) && !empty($gender)) {
            $data->whereIn('gender', $gender);
        }
        $data->orderBy('id', 'DESC');
        $data = $data->get();
        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }
    public function getMeanPupilId($conditions) {
        $year = $conditions['accyear'];
        $yr_selection = $conditions['syrs'];
        $your_school = $conditions['school_id'];
        $type = $conditions['type'];
        
        if (isset($conditions['gender']) & !empty($conditions['gender'])) {
            $gender = $conditions['gender'];
        } else {
            $gender = '';
        }
        $meanQuery = Model_dat_mean::year($year)
            ->select('school_id')
            ->distinct('school_id')
            ->whereIn('yr_selection', $yr_selection)
            ->where('school_id', '!=', $your_school)
            ->where('type', $type);
        if (isset($gender) && !empty($gender)) {
            $meanQuery->whereIn('gender', $gender);
        } 
        $meanQuery->orderBy('school_id', 'ASC');
        $data2 = $meanQuery->get();
        $result = FALSE;
        if ($data2) {
            $result = $data2;
        }
        return $result;
    }
    public function truncDatMean($acdyear) {
        $year = $acdyear;
        $tcTable = Model_dat_mean::year($year)->truncate();
    }
    public function addDatMean($year, $mean_array) {
        $save = new Model_dat_mean;
        $save->setYear($year);
        $save->school_id = $mean_array['school_id'];
        $save->gender = $mean_array['gender'];
        $save->yr_selection = $mean_array['yr_selection'];
        $save->type = $mean_array['type'];
        $save->score_data = $mean_array['score_data'];
        $save->date_time = $mean_array['date_time'];
        if ($save->save()) {
            $result['status'] = true;
        } else {
            $result['status'] = false;
        }
        return $result;
    }

}
