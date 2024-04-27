<?php

namespace App\Models\Dbschools;

use App\Util\DBSort\Sort;
use App\Util\DBFilter\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Traits\Searchable;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Schema;
use App\Services\ActionPlanMetaServiceProvider;

class Model_ass_main extends Model
{

    use Searchable;

    protected $table = "ass_main";
    protected $year = null;
    protected $connection = "schools";
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    public function setYear($year)
    {
        $this->year = $year; // Set protected year value to passed year
        if ($year != null) {
            $this->table = $this->getTable() . '_' . $year; // Set table name to arr_year_$year
        }
    }

    public static function year($year)
    { // create instance for dynamic year value
        $instance = new static;
        $instance->setYear($year);
        return $instance->newQuery();
    }

    public function getPupilLastAssessment($year)
    {
        return  Model_ass_main::year($year)
            ->where('is_completed', 'Y')
            ->first();
    }

    public static function getLastAssessment($ass_data)
    {
        $year = $ass_data["academic_year"];
        $user_id = $ass_data["user_id"];
        $assessment_sid = $ass_data["assessment_sid"];
        $platform_type = $ass_data["platform_type"];

        $data = Model_ass_main::year($year)
            ->where('pupil_id', $user_id)
            ->where('assessment_sid', $assessment_sid)
            ->where('platform', $platform_type)
            ->orderBy('started_date', 'DESC')
            ->limit(1)
            ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function deleteData($academic_year, $ass_main_table_id)
    {
        $result = Model_ass_main::year($academic_year)
            ->where('id', $ass_main_table_id)
            ->delete();
        return $result;
    }

    public function getNumberOfAssessmentAttendedByUser($academic_year, $user_id)
    {
        $data = Model_ass_main::year($academic_year)
            ->where('pupil_id', $user_id)
            ->where('is_completed', "Y")
            ->where(function ($q) {
                $q->where("platform", 2)
                    ->orWhere("platform", 3);
            })
            ->orderBy('started_date', 'DESC')
            ->count();

        $result = 0;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function NumberOfAssessmentAttendedByUser($academic_year, $user_id)
    {
        $data = Model_ass_main::year($academic_year)
            ->where('pupil_id', $user_id)
            ->where('is_completed', "Y")
            ->where(function ($q) {
                $q->where("platform", 2)
                    ->orWhere("platform", 3)
                    ->orWhere("platform", 5);
            })
            ->count();

        $result = 0;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function addNew($academic_year, $assessment_sid, $user_id, $session_code, $current_date_1, $platform, $completed_date = "", $round)
    {

        $store_data = new Model_ass_main;
        $store_data->setYear($academic_year);
        $store_data->pupil_id = $user_id;
        $store_data->assessment_sid = $assessment_sid;
        $store_data->session_code = $session_code;
        $store_data->started_date = $current_date_1;
        $store_data->round = $round;
        $store_data->platform = $platform;
        $store_data->is_completed = 'N';
        $store_data->completed_date = $completed_date;
        if ($store_data->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $store_data->id;
        } else {
            $tmp['status'] = false;
        }
        return $tmp;
    }



    public function storeAssEntryInAssMainTable($academic_year, $assessment_sid, $user_id, $session_code, $current_date_1, $platform, $completed_date = "")
    {

        $store_data = new Model_ass_main;
        $store_data->setYear($academic_year);

        $store_data->pupil_id = $user_id;
        $store_data->assessment_sid = $assessment_sid;
        $store_data->session_code = $session_code;
        $store_data->started_date = $current_date_1;
        $store_data->platform = $platform;
        if ($platform == 3) {
            $store_data->is_completed = 'Y';
            $store_data->completed_date = $completed_date;
        }
        //        $getdata = Model_ass_main::year($academic_year)->where('pupil_id', $user_id)->first();
        //        if (empty($getdata)) {
        if ($store_data->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $store_data->id;
        } else {
            $tmp['status'] = false;
        }
        //        } else {
        //            $tmp['status'] = true;
        //            $tmp['last_id'] = $getdata->id;
        //        }
        return $tmp;
    }

    public function getAssessment($dataArr)
    {

        $year = $dataArr['year'];
        $is_completed = $dataArr['is_completed'];
        $platform = $dataArr['platform'];
        $pop_id = $dataArr['pop_id'];
        $school_id = $dataArr['school_id'];

        $type = $dataArr['type'];
        $month = $dataArr['month'];
        $academicyear = $dataArr['academicyear'];
        $academicyear_start = $dataArr['academicyear_start'];
        $academicyear_end = $dataArr['academicyear_end'];
        $academicyear_close = $dataArr['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);


        $data = Model_ass_main::year($year);
        $data->select('ass_rawdata.id');
        $data->join('ass_rawdata_' . $year . ' as ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id');

        $data->where(function ($query) use ($start_end_date) {
            $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
        });
        $data->where(function ($query) use ($month) {
            $query->whereIn(DB::raw('SUBSTRING(ref,-8,2)'), $month);
        });

        $data->where('ass_rawdata.pop_id', $pop_id);
        $data->where('ass_rawdata.school_id', $school_id);
        $data->whereIn('ass_rawdata.type', $type);
        $data->where('ass_main_' . $year . '.is_completed', $is_completed);
        $data->whereIn('ass_main_' . $year . '.platform', $platform);
        $data = $data->get();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getCohartAssessment($dataArr)
    {
        $order_by = "cast(ass_rawdata.id AS SIGNED) DESC";

        $year = $dataArr['year'];
        $is_completed = $dataArr['is_completed'];
        $platform = $dataArr['platform'];
        $pop_id = $dataArr['pop_id'];
        $school_id = $dataArr['school_id'];

        $type = $dataArr['type'];
        $month = $dataArr['month'];
        $academicyear = $dataArr['academicyear'];
        $academicyear_start = $dataArr['academicyear_start'];
        $academicyear_end = $dataArr['academicyear_end'];
        $academicyear_close = $dataArr['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);
        $special_at = $dataArr['special_at'];


        $data = Model_ass_main::year($year);
        $data->select('ass_rawdata.*');
        $data->join('ass_rawdata_' . $year . ' as ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id');

        $data->where(function ($query) use ($start_end_date) {
            $query->whereBetween('datetime', $start_end_date);
        });
        $data->where(function ($query) use ($month) {
            $query->whereIn(DB::raw('SUBSTRING(datetime,5,2)'), $month);
        });

        if (isset($special_at) && !empty($special_at)) {
            $data->where($special_at);
        }

        $data->where('ass_rawdata.pop_id', $pop_id);
        $data->where('ass_rawdata.school_id', $school_id);
        $data->whereIn('ass_rawdata.type', $type);
        $data->where('ass_main_' . $year . '.is_completed', $is_completed);
        $data->whereIn('ass_main_' . $year . '.platform', $platform);
        $data->orderByRaw($order_by);
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssesmentId($dataArr)
    {
        $year = $dataArr['year'];
        $is_completed = $dataArr['is_completed'];
        $platform = $dataArr['platform'];
        $pupil_id = $dataArr['pupil_id'];


        $data = Model_ass_main::year($year);
        $data->select('*');
        $data->where('pupil_id', $pupil_id);
        $data->whereIn('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->orderBy('id', 'DESC');
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getPreviousAssesmentId($dataArr)
    {
        $year = $dataArr['year'];
        $is_completed = $dataArr['is_completed'];
        $platform = $dataArr['platform'];
        $pupil_id = $dataArr['pupil_id'];

        $data = Model_ass_main::year($year);
        $data->select('*');
        $data->where('pupil_id', $pupil_id);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->orderBy('id', 'DESC');
        $data->limit(1);
        $data->offset(1);
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function assessmentCompleted($year, $ass_main_table_id, $completed_date, $user_id, $is_called_from_newuiux = false)
    {
        $getdata = Model_ass_main::year($year)->where('pupil_id', $user_id)->first();
        $round = $getdata->round;
        if ($round >= 0) {
            $round++;
            $data = Model_ass_main::year($year);
            $data->where('id', $ass_main_table_id);
            if ($is_called_from_newuiux == true) {
                $data->update(['is_completed' => 'Y', 'completed_date' => $completed_date]);
            } else {
                $data->update(['is_completed' => 'Y', 'completed_date' => $completed_date, 'round' => $round]);
            }
        }
        return TRUE;
    }

    public function getMainTable($year, $main_id, $user_id)
    {
        $data = Model_ass_main::year($year)
            ->where('id', $main_id)
            ->where('pupil_id', $user_id)
            ->first();

        $result = FALSE;
        if (isset($data) && !empty($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAssMainData($condition)
    {
        $year = $condition['year'];
        if (isset($condition['pupil_id_array'])) {
            $pop_id = $condition['pupil_id_array'];
        } else {
            $pop_id = array($condition['pupil_id']);
        }
        $platform = $condition['platform'];
        $query = Model_ass_main::year($year);
        $query->whereIn('pupil_id', $pop_id);
        $query->whereIn('platform', $platform);
        $query->orderBy('completed_date', 'DESC');

        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllData_ForNewUI($conditions)
    {
        $year = $conditions['year'];
        $pop_id = $conditions['pupil_id'];
        $platform = $conditions['platform'];
        $months = $conditions['months'];
        $rounds = $conditions['rounds'];
        $type = array('self', 'p', 'at', 'sch', 'hs');
        $query = Model_ass_main::year($year);
                if(isset($conditions['rounds']) && !empty($conditions['rounds'])){
                    $query->select('ass_rawdata_' . $year . '.id as ass_raw_id', 'ass_main_' . $year . '.id', 'ass_main_' . $year . '.round', 'ass_rawdata_' . $year . '.*', 'ass_score_' . $year . '.P', 'ass_score_' . $year . '.S', 'ass_score_' . $year . '.L', 'ass_score_' . $year . '.X',
                        'ass_score_' . $year . '.datetime');
                }else{
                    $query->select('ass_rawdata_' . $year . '.id as ass_raw_id', 'ass_main_' . $year . '.id', 'ass_rawdata_' . $year . '.*', 'ass_score_' . $year . '.P', 'ass_score_' . $year . '.S', 'ass_score_' . $year . '.L', 'ass_score_' . $year . '.X',
                            'ass_score_' . $year . '.datetime');
                }
                $query->leftjoin('ass_rawdata_' . $year, 'ass_main_' . $year . '.id', '=', 'ass_rawdata_' . $year . '.ass_main_id',)
                ->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id')
                ->whereIn('ass_main_' . $year . '.pupil_id', $pop_id)
                ->whereIn('ass_main_' . $year . '.platform', $platform)
                ->whereIn('ass_rawdata_' . $year . '.pop_id', $pop_id);
                if (isset($conditions['academicyear_close']) && !empty($conditions['academicyear_close'])) {
                    $query = $query->whereIn(DB::raw('SUBSTRING(ass_rawdata_' . $year . '.datetime,5,2)'), $months);
                }
                if (isset($conditions['rounds']) && !empty($conditions['rounds'])) {
                    $query = $query->whereIn('ass_main_' . $year . '.round', $rounds);
                }
                $query->whereIn('ass_score_' . $year . '.pop_id', $pop_id)
                ->orderBy('ass_rawdata_' . $year . '.id', 'DESC');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllData($conditions)
    {
        $year = $conditions['year'];
        $pop_id = $conditions['pupil_id'];
        $platform = $conditions['platform'];
        $months = $conditions['months'];
        $rounds = $conditions['rounds'];
        $type = array('self', 'p', 'at', 'sch', 'hs');
        $query = Model_ass_main::year($year);
        if (isset($conditions['rounds']) && !empty($conditions['rounds'])) {
            $query->select(
                'ass_rawdata_' . $year . '.id as ass_raw_id',
                'ass_main_' . $year . '.id',
                'ass_main_' . $year . '.round as ass_round',
                'ass_rawdata_' . $year . '.*',
                'ass_score_' . $year . '.P',
                'ass_score_' . $year . '.S',
                'ass_score_' . $year . '.L',
                'ass_score_' . $year . '.X',
                'ass_score_' . $year . '.datetime'
            );
        } else {
            $query->select(
                'ass_rawdata_' . $year . '.id as ass_raw_id',
                'ass_main_' . $year . '.id',
                'ass_rawdata_' . $year . '.*',
                'ass_score_' . $year . '.P',
                'ass_score_' . $year . '.S',
                'ass_score_' . $year . '.L',
                'ass_score_' . $year . '.X',
                'ass_score_' . $year . '.datetime'
            );
        }
        $query->leftjoin('ass_rawdata_' . $year, 'ass_main_' . $year . '.id', '=', 'ass_rawdata_' . $year . '.ass_main_id',)
            ->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id')
            ->whereIn('ass_main_' . $year . '.pupil_id', $pop_id)
            ->whereIn('ass_main_' . $year . '.platform', $platform)
            ->whereIn('ass_rawdata_' . $year . '.pop_id', $pop_id);
        if (isset($conditions['academicyear_close']) && !empty($conditions['academicyear_close'])) {
            $query = $query->whereIn(DB::raw('SUBSTRING(ass_rawdata_' . $year . '.datetime,5,2)'), $months);
        }
        if (isset($conditions['rounds']) && !empty($conditions['rounds'])) {
            $query = $query->whereIn('ass_main_' . $year . '.round', $rounds);
        }
        $query->whereIn('ass_score_' . $year . '.pop_id', $pop_id)
            ->orderBy('ass_rawdata_' . $year . '.id', 'DESC');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getBothAssmentActionPlan($condition)
    {
        $year = $condition['year'];
        $pop_id = $condition['pop_id'];
        $data = Model_ass_main::year($year);
        $data->where('pupil_id', $pop_id);
        $data->orderBy('id', 'DESC');
        $data->limit(2);
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getPastAssmentActionPlan($condition)
    {
        $year = $condition['year'];
        $pop_id = $condition['pop_id'];
        $data = Model_ass_main::year($year);
        $data->where('pupil_id', $pop_id);
        $data->orderBy('id', 'DESC');
        //        $data->limit(1);
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssmentByPopId($condition)
    {
        $year = $condition['year'];
        $pop_id = $condition['pupil_id'];
        $is_completed = $condition['is_completed'];
        $platform = $condition['platform'];
        $data = Model_ass_main::year($year);
        $data->where('pupil_id', $pop_id);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->orderBy('id', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getFilterAssessmentComplted($condition)
    {
        //        if ($condition['pupil_id'] == '79601') {
        //            DB::connection('schools')->enableQueryLog();
        //        }
        $year = $condition['year'];
        $is_completed = $condition['is_completed'];
        $platform = $condition['platform'];
        $pop_id = $condition['pupil_id'];
        $school_id = $condition['school_id'];

        $type = $condition['type'];
        $month = $condition['month'];
        $academicyear = $condition['academicyear'];
        $academicyear_start = $condition['academicyear_start'];
        $academicyear_end = $condition['academicyear_end'];
        $academicyear_close = $condition['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);


        $data = Model_ass_main::year($year);
        $data->select('ass_rawdata.id');
        $data->join('ass_rawdata_' . $year . ' as ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id');

        $data->where(function ($query) use ($start_end_date) {
            $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
        });
        $data->where(function ($query) use ($month) {
            $query->whereIn(DB::raw('SUBSTRING(ref,-8,2)'), $month);
        });

        $data->where('ass_rawdata.pop_id', $pop_id);
        $data->where('ass_rawdata.school_id', $school_id);
        $data->whereIn('ass_rawdata.type', $type);
        $data->whereIn('ass_main_' . $year . '.is_completed', $is_completed);
        $data->whereIn('ass_main_' . $year . '.platform', $platform);
        $data = $data->get();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getFilterAssessmentCompltedDescFirst($condition)
    {
        //        if ($condition['pupil_id'] == '79601') {
        //            DB::connection('schools')->enableQueryLog();
        //        }
        $year = $condition['year'];
        $is_completed = $condition['is_completed'];
        $platform = $condition['platform'];
        $pop_id = $condition['pupil_id'];
        $school_id = $condition['school_id'];

        $type = $condition['type'];
        $month = $condition['month'];
        $academicyear = $condition['academicyear'];
        $academicyear_start = $condition['academicyear_start'];
        $academicyear_end = $condition['academicyear_end'];
        $academicyear_close = $condition['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);


        $data = Model_ass_main::year($year);
        $data->select('ass_main_' . $year . '.id', 'ass_main_' . $year . '.is_completed');
        $data->join('ass_rawdata_' . $year . ' as ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id');

        $data->where(function ($query) use ($start_end_date) {
            $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
        });
        $data->where(function ($query) use ($month) {
            $query->whereIn(DB::raw('SUBSTRING(ref,-8,2)'), $month);
        });

        $data->where('ass_rawdata.pop_id', $pop_id);
        $data->where('ass_rawdata.school_id', $school_id);
        $data->whereIn('ass_rawdata.type', $type);
        $data->whereIn('ass_main_' . $year . '.is_completed', $is_completed);
        $data->whereIn('ass_main_' . $year . '.platform', $platform);
        $data->orderBy('id', 'DESC');
        $data = $data->first();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getStartAssmentByMonth($condition)
    {
        $year = $condition['year'];
        $pop_id = $condition['pupil_id'];
        $platform = $condition['platform'];
        $month = $condition['month'];
        //        DB::connection('schools')->enableQueryLog();
        $query = Model_ass_main::year($year)
            ->whereIn('pupil_id', $pop_id)
            ->whereIn('platform', $platform)
            ->whereIn(\DB::raw("SUBSTRING(started_date, 6,2)"), $month)
            ->groupBy('pupil_id')
            ->orderBy('id', 'ASC');

        $data = $query->get();
        //        $q = DB::connection('schools')->getQueryLog();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAssessmentData($academicyear)
    {
        $data = Model_ass_main::year($academicyear);
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function deletePupil($academicyear, $conditions)
    {
        $result = Model_ass_main::year($academicyear)
            ->where($conditions)
            ->delete();
        return $result;
    }

    public function getAssMainData($academicyear, $pop_id)
    {
        $data = Model_ass_main::year($academicyear)
            ->where('pupil_id', $pop_id);
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssMainDataOrderByTime($academicyear, $pop_id)
    {
        $data = Model_ass_main::year($academicyear)
            ->where('pupil_id', $pop_id)
            ->orderBy('started_date', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getLastAssMainData($academicyear)
    {
        $data = Model_ass_main::year($academicyear)
            ->orderBy('id', 'DESC');
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getDataByAssmainId($academic_year, $ass_main_id)
    {
        $data = Model_ass_main::year($academic_year)
            ->where("id", $ass_main_id)
            ->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function storeAssEntryInAssMainTableWithID($academicyear, $data)
    {
        $saveData = Model_ass_main::year($academicyear);
        $data = $saveData->insert($data);
        $result = FALSE;
        if ($data) {
            $result = TRUE;
        }
        return $result;
    }

    public function getAllHalfAssessmentData($academicyear)
    {
        $data = Model_ass_main::year($academicyear)
            ->select('id', 'pupil_id', 'started_date')
            ->distinct('pupil_id')
            ->where('is_completed', 'N')
            ->where('platform', '!=', '5');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getHalfAssessmentData($academicyear, $conditions)
    {
        $data = Model_ass_main::year($academicyear)
            ->where($conditions)
            ->orderBy('id', 'DESC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssMainDataByDate($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];

        if (isset($conditions['month']) && !empty($conditions['month'])) {
            $month = $conditions['month'];
        }
        if (isset($conditions['academicyear']) && !empty($conditions['academicyear'])) {
            $academicyear = $conditions['academicyear'];
        }
        if (isset($conditions['academicyear_close']) && !empty($conditions['academicyear_close'])) {
            $academicyear_close = $conditions['academicyear_close'];
        }

        if (isset($conditions['pupil_id']) && !empty($conditions['pupil_id'])) {
            $pupil_id = $conditions['pupil_id'];
        }

        $start = $academicyear . '-' . $month[0];
        $end = $academicyear_close . '-' . $month[1] . '-31';
        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->where('pupil_id', $pupil_id);
        if (isset($start) && isset($end)) {
            $data->where('completed_date', '>=', $start)
                ->where('completed_date', '<=', $end);
        }
        $data->orderBy('id', 'DESC');
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function updateData($year, $ass_main_table_id, $update_array)
    {
        $data = Model_ass_main::year($year);
        $data->where('id', $ass_main_table_id);
        $data->update($update_array);
        return TRUE;
    }

    public function getAssMainIds($year)
    {
        $data = Model_ass_main::year($year)
            ->pluck('pupil_id')
            ->toArray();

        return $data;
    }

    public function getAssesmentYearWise($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];
        $round = $conditions['round'];

        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->whereIn('round', $round);
        $data->orderBy('id', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function assesmentcomplatepupilcount($year, $round, $status)
    {
        $data = Model_ass_main::year($year);
        $data->whereIn('round', $round);
        $data->whereIn('is_completed', $status);
        $data = $data->get();
        $result = FALSE;
        if (!empty($data)) {
            $result = $data;
        }
        return $result;
    }

    public function postassesmentpupilcount($year, $round)
    {
        $data = Model_ass_main::year($year);
        $data->whereIn('round', $round);
        $data = $data->get();
        $result = FALSE;
        if (!empty($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssMainRound($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];
        if (isset($conditions['round']) && !empty($conditions['round'])) {
            $round = $conditions['round'];
        }
        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->where('round', $round);
        $data->orderBy('id', 'DESC');
        $data = $data->get();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getassesmentGC($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];
        $round = $conditions['round'];
        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        if ($round >= 3) {
            $data->where('round', $round);
        } else if ($round == 4) {
            $data->where('round', '>=', $round);
        }
        $data->orderBy('id', 'DESC');
        $data = $data->get();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getassesmentG($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];
        $round = $conditions['round'];
        $type = $conditions['type'];
        $data = Model_ass_main::year($year);
        $data->select('ass_main_' . $year . '.pupil_id');
        $data->distinct('ass_main_' . $year . '.pupil_id');
        $data->leftjoin('population', 'ass_main_' . $year . '.pupil_id', '=', 'population.id');
        $data->leftjoin('ass_score_' . $year, 'ass_score_' . $year . '.pop_id', '=', 'population.id');
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        if ($round >= 3) {
            $data->where('round', $round);
        } else if ($round == 4) {
            $data->where('round', '>=', $round);
        }
        if ($type == 'hs') {
            $type_array = array('hs', 'sch');
            $data->whereIn('ass_score_' . $year . '.type', $type_array);
        } else {
            $data->where('ass_score_' . $year . '.type', 'at');
        }
        $data->where("population.lastname", "!=", 'testpupil');
        $data = $data->get();

        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssMainComData($conditions)
    {
        if (isset($conditions['round']) && !empty($conditions['round']) && $conditions['round'] == 1) {
            $round = [1];
        } elseif (isset($conditions['round']) && !empty($conditions['round']) && $conditions['round'] == 2) {
            $round = [1, 2];
        } elseif (isset($conditions['round']) && !empty($conditions['round']) && $conditions['round'] == 3) {
            $round = [2, 3];
        }
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $platform = $conditions['platform'];
        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        $data->whereIn('platform', $platform);
        $data->whereIn('round', $round);
        $data->orderBy('id', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getPupilCompletedAss($accyear, $pupil_id, $month)
    {
        $platform = [2, 3, 5];
        $query = Model_ass_main::year($accyear);
        $query->where('pupil_id', $pupil_id);
        $query->whereIn('platform', $platform);
        $query->where('is_completed', 'Y');
        if (isset($month) && !empty($month)) {
            $query->Where(function ($data) use ($month) {
                $data->whereIn(DB::raw('SUBSTRING(started_date,6,2)'), $month);
            });
        }
        $query->orderBy('id', 'DESC');
        $data = $query->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function countpupildata($conditions)
    {
        $year = $conditions['year'];
        $is_completed = $conditions['is_completed'];
        $date = $conditions['date'];
        $data = Model_ass_main::year($year);
        $data->where('is_completed', $is_completed);
        if ($is_completed == "Y") {
            $data->whereDate('completed_date', $date);
        } else {
            $data->whereDate('started_date', $date);
        }
        $data = $data->get();
        $result = "";
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAssMainDataWithPupilIds($condition)
    {
        $year = $condition['year'];
        $pop_id = $condition['pupil_id'];
        $platform = $condition['platform'];
        $query = Model_ass_main::year($year);
        $query->whereIn('pupil_id', $pop_id);
        $query->whereIn('platform', $platform);
        $query->orderBy('completed_date', 'DESC');
        $data = $query->get()->toArray();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAssMainDatabuPupils($condition)
    {
        $school_id = $condition['school_id'] ?? "";
        $year = $condition['year'];
        $pop_id = $condition['pupil_id'];
        $type = $condition['type'];
        if (isset($condition['platform']) && !empty($condition['platform'])) {
            $platform = $condition['platform'];
        }
        if (isset($condition['month']) && !empty($condition['month'])) {
            $month = $condition['month'];
        } else {
            $month = '';
        }
        if (isset($condition['round']) && !empty($condition['round'])) {
            $round = $condition['round'];
        } else {
            $round = '';
        }
        if (isset($condition['academicyear']) && !empty($condition['academicyear'])) {
            $academicyear = $condition['academicyear'];
        }
        if (isset($condition['academicyear_start']) && !empty($condition['academicyear_start'])) {
            $academicyear_start = $condition['academicyear_start'];
        }
        if (isset($condition['academicyear_end']) && !empty($condition['academicyear_end'])) {
            $academicyear_end = $condition['academicyear_end'];
        }
        if (isset($condition['academicyear_close']) && !empty($condition['academicyear_close'])) {
            $academicyear_close = $condition['academicyear_close'];
        }
        if ((isset($academicyear) && !empty($academicyear)) && (isset($academicyear_start) && !empty($academicyear_start))) {
            $start_date = $academicyear . $academicyear_start . '010000';
        } else {
            $start_date = "";
        }
        if ((isset($academicyear_close) && !empty($academicyear_close)) && (isset($academicyear_end) && !empty($academicyear_end))) {
            $end_date = $academicyear_close . $academicyear_end . '312359';
        } else {
            $end_date = "";
        }
        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $start_end_date = array($start_date, $end_date);
        }
        if (isset($condition['ass_main_id']) && !empty($condition['ass_main_id'])) {
            $ass_main_id = $condition['ass_main_id'];
        } else {
            $ass_main_id = '';
        }
        if (isset($condition['ass_raw_datetime']) && !empty($condition['ass_raw_datetime'])) {
            $ass_raw_datetime = $condition['ass_raw_datetime'];
        } else {
            $ass_raw_datetime = '';
        }
        if (is_array($month)) {
            $month = implode(',', $month);
        } else {
            $month = '';
        }
        if (is_array($ass_main_id)) {
            $ass_main_id = '"' . implode(',', $ass_main_id) . '"';
        } else {
            $ass_main_id = '';
        }
        if (isset($condition['completed']) && !empty($condition['completed'])) {
            $completed = $condition['completed'];
        } else {
            $completed = '';
        }
        if (empty($school_id)) {
        $school_id = mySchoolId();
        }
        if (in_array($school_id, getUiSchoolID())) {
            $data = DB::connection("schools")->select('call get_filter(?,?,?,?,?,?,?,?,?)', array($year, $pop_id, $month, $start_date, $end_date, $ass_main_id, $ass_raw_datetime, $round, $completed));
        } else {
            $data = DB::connection("schools")->select('call get_filter(?,?,?,?,?,?,?)', array($year, $pop_id, $month, $start_date, $end_date, $ass_main_id, $ass_raw_datetime));
        }
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getTableColumns($year)
    {
        $table_name = "ass_main_" . $year . "";
        $tbl_selected = DB::connection('schools')->select("SHOW COLUMNS FROM $table_name");
        $tbl_selected = array_map('current', $tbl_selected);
        return $tbl_selected;
    }

    public function createNewColumn($year, $column_name)
    {
        $table_name = "ass_main_" . $year . "";
        $tbl_selected = DB::connection('schools')->select("ALTER TABLE $table_name ADD $column_name VARCHAR(200)");
        return $this->getTableColumns($year);
    }

    public function updateAssessmentMain($ass_main_id, $response, $year)
    {
        $toUpdate = Model_ass_main::year($year)->find($ass_main_id);
        if($toUpdate){
            $toUpdate->is_manipulated = $toUpdate->is_manipulated == 1 ? $toUpdate->is_manipulated : $response;
            $toUpdate->save();
        }
        return true;
    }

    public function FetchAllMainData($year)
    {
        $result = Model_ass_main::year($year)
            ->get();
        return $result;
    }

    public function FetchMainDataBySpeed($year)
    {
        $result = Model_ass_main::year($year)->whereNull('speed')->limit(150)->get();
        return $result;
    }

    public function FetchMainByManipulated($year)
    {
        $result = Model_ass_main::year($year)->whereNotNull('is_manipulated')->where('is_completed', 'Y')
            ->orderBy('id', 'DESC')->first();
        return $result;
    }

    public function FindandUpdateSpeedColumn($speed, $ass_main_id, $year)
    {
        $toUpdate = Model_ass_main::year($year)->find($ass_main_id);
        $toUpdate->speed = $speed;
        $toUpdate->save();
        return true;
    }

    public function getRounds($acyear)
    {
        $data = Model_ass_main::year($acyear)
            ->select('round')
            ->where('is_completed', 'Y')
            ->groupBy('round')
            ->get();
        $result = FALSE;
        if ($data) {
            $flattened_round = Arr::flatten($data->toArray());
            $result = $flattened_round;
        }
        return $result;
    }

    public function getPupilList($filter)
    {
        foreach ($filter['academic_year'] as $academicyear) {

            $query = Model_ass_main::year($academicyear);
            $query = $query->select('ass_main_' . $academicyear . '.*', 'population.firstname', 'population.lastname', 'population.gender', 'population.username', 'population.id', 'ass_main_' . $academicyear . '.id as ass_main_id');

            $query = $query->leftjoin('population', 'population.id', '=', 'ass_main_' . $academicyear . '.pupil_id')
                ->Where('ass_main_' . $academicyear . '.is_completed', 'Y');
            $query = $query->where('population.level', 1);

            $data = $query->get();
            $finaldata[$academicyear] = $data;
        }

        return $finaldata;
    }

    public function CheckIfDataIsAvailable($academicyear)
    {
        $query = Model_ass_main::year($academicyear);
        $query = $query->select('*');
        return $query->get();
    }

    public function CheckIfDataIsAvailableStudent($academicyear, $pupil_id)
    {
        $query = Model_ass_main::year($academicyear);
        $query = $query->select('*')->where('pupil_id', $pupil_id);
        return $query->get();
    }

    public function CheckIfDataIsAvailableForParticularPupil($academicyear, $student_id, $round = null)
    {
        $query = Model_ass_main::year($academicyear);
        $query = $query->where('pupil_id', $student_id)->select('*')->where('is_completed', 'Y');
        if ($round != null) {
            $query = $query->where('round', $round);
        }
        return $query->orderBy('id', 'DESC')->first();
    }

    public function GetFirstCompletedAssessment($filter, $pupil_id = null)
    {
        foreach ($filter['academic_year'] as $academicyear) {
            $query = Model_ass_main::year($academicyear)->where('is_completed', 'Y')->whereIn('round', $filter['round']);
            if( isset($filter['campus']) ) {
                $query = $query->join('arr_year_'.$academicyear.' as arr_year', function ($join) use ($filter, $academicyear) {
                    $join->on('arr_year.name_id', '=', 'ass_main_'.$academicyear.'.pupil_id')
                         ->where('arr_year.field', 'campus')->whereIn('arr_year.value', $filter['campus']);
                });
            }
            if ($pupil_id != null)
                $query = $query->where('pupil_id', $pupil_id);
            return $query->first();
        }
    }

    public function GetFirstCompletedAssessmentHistory($filter)
    {
        $data = null;
        foreach ($filter['historyfilter'] as $historyfilter) {
            $data[ $historyfilter['academic_year'].'-'.$historyfilter['round'] ]  = Model_ass_main::year($historyfilter['academic_year'])
                ->where('is_completed', 'Y')
                ->where('round', $historyfilter['round'])
                ->first();
        }
        return $data;
    }

    public function round($year)
    {
        return Model_ass_main::year($year)->where('is_completed', 'Y')->max('round');
    }

    public function getMinRound($year)
    {
        return Model_ass_main::year($year)->where('is_completed', 'Y')->min('round');
    }

    public function getStudentLatestRound( $year, $pupil_id )
    {
        return Model_ass_main::year($year)->where('is_completed', 'Y')->where( 'pupil_id', $pupil_id )->max('round');
    }

    public function studentlatest($year, $round, $pupil_id)
    {
        return Model_ass_main::year($year)
                ->select('ass_main_'.$year.'.*')
                ->join('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
                ->where(['ass_main_'.$year.'.pupil_id' => $pupil_id, 'ass_main_'.$year.'.round' => $round])->orderBy('ass_main_'.$year.'.id', 'DESC')->first();
    }

    public static function checkAssForSameDay($pop_id, $curr, $academicyear)
    {

        $data = Model_ass_main::year($academicyear)
            ->where('pupil_id', $pop_id)
            ->where('started_date', 'like', '%' . $curr . '%')
            ->orderBy('id', 'DESC')
            ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }

        return $result;
    }

    public function Assessment($filter, $type)
    {
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.mis_id as student_mis_id',
                'population.firstname',
                'population.lastname',
                'arr_year.field',
                'arr_year.value',
                'ass_main_' . $academicyear . '.is_manipulated as is_manipulated',
                'ass_main_' . $academicyear . '.speed as speed',
                'ass_main_' . $academicyear . '.is_completed',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $academicyear . '.round',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')
            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);
        $query = $query->where('ass_main_' . $academicyear . '.is_completed', 'Y');
        $query = $query->whereIn('ass_rawdata.type', $type);
        $query = $query->where('population.level', 1);
        $query = $query->groupBy('ass_score.id');
        return $query;
    }


    public function GetStudentWithFactorBias($filter, $bias, $type, $polar)
    {
        $academicyear = $filter['academic_year'];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                'arr_year.field',
                'arr_year.value',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.ref', '=', 'ass_rawdata.ref')
            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);
        $query = $query->where('ass_main_' . $academicyear . '.is_completed', 'Y');
        $query = $query->whereIn('ass_rawdata.type', $type);
        $query = $query->where('population.level', 1);
        if ($bias != '' && $polar != '') {
            if ($polar == 'low') {
                $query = $query->Where('ass_score.' . $bias, '<=', 3);
            }
            if ($polar == 'high') {
                $query = $query->Where('ass_score.' . $bias, '>=', 12);
            }
        }
        $query = $query->groupBy('ass_score.id');
        $query = $query->get();
        return $query;
    }

    public function getSafeguardingAssessment($filter, $type, $meta, $studentlist, $when)
    {
        $academicyear = $filter['academic_year'][0];
        //$query = $this->Assessment( $filter, $type );
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                DB::raw("(SELECT value FROM arr_year_$academicyear WHERE field = 'name_code' AND arr_year_$academicyear.name_id = population.id) as name_code"),
                'arr_year.field',
                'arr_year.value',
                'ass_main_' . $academicyear . '.is_manipulated as is_manipulated',
                'ass_main_' . $academicyear . '.speed as speed',
                'ass_main_' . $academicyear . '.is_completed',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $academicyear . '.round',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')
            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')
            //->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.ref', '=', 'ass_rawdata.ref')
            //->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);

        if (count($studentlist) > 0) {
            $query = $query->where(
                function ($query) use ($academicyear, $type, $studentlist) {
                    return $query
                        ->whereIn('ass_score.pop_id', $studentlist)
                        ->where('ass_main_' . $academicyear . '.is_completed', 'Y')
                        ->whereIn('ass_rawdata.type', $type);
                }
            );
        } else {
            $query = $query->where(
                function ($query) use ($academicyear, $type) {
                    return $query
                        ->where('ass_main_' . $academicyear . '.is_completed', 'Y')
                        ->whereIn('ass_rawdata.type', $type);
                }
            );
        }



        // if( count($studentlist) > 0 )
        //     $query = $query->whereIn('ass_main_'.$academicyear.'.pupil_id', $studentlist );
        $query = $query->where('population.level', 1);

        if( in_array('at', $type) ) {
            $query = $query->orderBy('ass_main_' . $academicyear .'.out_of_school_polar_count', 'DESC');
            $query = $query->orderByRaw('ass_main_' . $academicyear .'.out_of_school_composite_count DESC');
        }else {
            $query = $query->orderBy('ass_main_' . $academicyear .'.in_school_polar_count', 'DESC');
            $query = $query->orderByRaw('ass_main_' . $academicyear .'.in_school_composite_count DESC');
        }

        $query = $query->groupBy('ass_score.id');
        if ($when == 'current') {
            $skip = $meta['page'] - 1;
            return $query->skip($skip)->paginate($meta['size']);
        } else {
            return $query->get();
        }
    }

    public function getAssessmentReport($filter, $type, $studentlist = [], $arr_year_data_type = null )
    {
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                'population.mis_id',
                'arr_year.field',
                'arr_year.value',
                'ass_main_' . $academicyear . '.is_manipulated as is_manipulated',
                'ass_main_' . $academicyear . '.speed as speed',
                'ass_main_' . $academicyear . '.is_completed',
                'ass_main_' . $academicyear . '.completed_date',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $academicyear . '.round',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')
            //->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.ref', '=', 'ass_rawdata.ref')

            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')

            //->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);

        if (count($studentlist) > 0) {
            $query = $query->where(
                function ($query) use ($academicyear, $type, $studentlist) {
                    return $query
                        ->where('ass_main_' . $academicyear . '.is_completed', 'Y')
                        ->whereIn('ass_rawdata.type', $type)
                        ->whereIn('ass_score.pop_id', $studentlist);
                }
            );
        } else {
            $query = $query->where(
                function ($query) use ($academicyear, $type) {
                    return $query
                        ->where('ass_main_' . $academicyear . '.is_completed', 'Y')
                        ->whereIn('ass_rawdata.type', $type);
                }
            );
        }

        if( isset($filter['gender']) ) {
            $query = $query->where('population.gender', $filter['gender']);
        }

        if( $arr_year_data_type ) {
            $query = $query->whereIn('arr_year.field', $arr_year_data_type );
        }

        $query = $query->where('population.level', 1);
        $query = $query->groupBy('ass_score.id');
        //$query = $this->Assessment($filter, $type);
        return $query->get()->toArray();
    }

    public function getAssessmentReportByBias($filter, $type, $bias )
    {
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                'arr_year.field',
                'arr_year.value',
                'ass_main_' . $academicyear . '.is_manipulated as is_manipulated',
                'ass_main_' . $academicyear . '.speed as speed',
                'ass_main_' . $academicyear . '.is_completed',
                'ass_main_' . $academicyear . '.completed_date',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $academicyear . '.round',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')

            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')

            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);
        $query = $query->where(
            function ($query) use ($academicyear, $type) {
                return $query
                    ->where('ass_main_' . $academicyear . '.is_completed', 'Y')
                    ->whereIn('ass_rawdata.type', $type);
            }
        );
        
        if( isset($filter['gender']) ) {
            $query = $query->where('population.gender', $filter['gender']);
        }

        $query = $query->where('population.level', 1);
        $query = $query->groupBy('ass_score.id');
        return $query->get()->toArray();
    }


    public function saveAssessmentRound($year, $ass_main_id, $round)
    {
        $data = Model_ass_main::year($year);
        $data->whereIn('id', $ass_main_id);
        $data->update(['round' => $round]);
        return TRUE;
    }

    public function getDistinctRound($year)
    {
        $data = Model_ass_main::year($year)->select('round')->distinct('round')->get();
        return $data;
    }

    public function AssessmentOnRound($year, $round, $count = FALSE)
    {
        $data = Model_ass_main::year($year)->where('round', $round);
        $data = $count ? $data->count() : $data->get();
        return $data;
    }

    public function AssessmentOnRoundGender($year, $round, $gender, $count = FALSE)
    {
        $data = Model_ass_main::year($year)
            ->select('population.id as student_id', 'population.level', 'population.firstname', 'population.lastname', 'population.dob', 'population.gender', 'population.password', 'population.username', 'ass_main_' . $year . '.*')
            ->leftjoin('population', 'population.id', '=', 'ass_main_' . $year . '.pupil_id')
            ->where('ass_main_' . $year . '.round', $round)
            ->where('ass_main_' . $year . '.is_completed', 'Y')
            ->where('population.level', 1)
            ->where('population.gender', 'LIKE', $gender);
        $data = $count ? $data->count() : $data->get();
        return $data;
    }

    public function AssessmentOnRoundGenderAndType($type, $year, $round, $gender, $count = FALSE)
    {
        $t = $type == "IN_SCHOOL" ? "sch" : "at";
        $data = Model_ass_main::year($year)
            ->select('population.id as student_id', 'population.level', 'population.firstname', 'population.lastname', 'population.dob', 'population.gender', 'population.password', 'population.username', 'ass_main_' . $year . '.*', 'ass_rawdata.ass_main_id', 'ass_rawdata.type')
            ->leftjoin('population', 'population.id', '=', 'ass_main_' . $year . '.pupil_id')
            ->leftjoin('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->where('ass_main_' . $year . '.round', $round)
            ->where('ass_main_' . $year . '.is_completed', 'Y')
            ->where('population.level', 1)
            ->where('population.gender', 'LIKE', $gender)
            ->where('ass_rawdata.type', $t);
        $data = $count ? $data->count() : $data->get();
        return $data;
    }

    public function getFactorScore($year, $ass_main_id, $type, $bias, $pupil_id)
    {
        $res = null;
        $data = Model_ass_rawdata::year($year)
            ->select('ass_score.*')
            ->leftjoin('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata_' . $year . '.id')
            ->whereIn('ass_rawdata_' . $year . '.type', $type)
            ->where('ass_rawdata_' . $year . '.pop_id', $pupil_id)
            ->where('ass_rawdata_' . $year . '.ass_main_id', $ass_main_id)
            ->orderBy('ass_rawdata_' . $year . '.id', 'DESC')
            ->first();
        if ($data) {
            $res = (float)$data->{$bias};
        }
        return $res;
    }

    public function getPolarBiases($year, $ass_main_id, $type, $pupil_id)
    {
        $res = [];
        $data = Model_ass_rawdata::year($year)
            ->select('ass_score.*')
            ->leftjoin('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata_' . $year . '.id')
            ->whereIn('ass_rawdata_' . $year . '.type', $type)
            ->where('ass_rawdata_' . $year . '.pop_id', $pupil_id)
            ->where('ass_rawdata_' . $year . '.ass_main_id', $ass_main_id)
            ->orderBy('ass_rawdata_' . $year . '.id', 'DESC')
            ->first();
        if ($data) {
            $p = $this->getBiasName($data->P);
            $s = $this->getBiasName($data->S);
            $l = $this->getBiasName($data->L);
            $x = $this->getBiasName($data->X);
            if ($p != '') {
                $label = $p . "Self Disclosure";
                $type = strtoupper(str_replace(' ', '_', $label));
                $res[] = [
                    "value" => (float)$data->P,
                    "label" => $label,
                    "type" => $type,
                ];
            }
            if ($s != '') {
                $label = $s . "Trust Of Self";
                $type = strtoupper(str_replace(' ', '_', $label));
                $res[] = [
                    "value" => (float)$data->S,
                    "label" => $label,
                    "type" => $type,
                ];
            }
            if ($l != '') {
                $label = $l . "Trust Of Others";
                $type = strtoupper(str_replace(' ', '_', $label));
                $res[] = [
                    "value" => (float)$data->L,
                    "label" => $label,
                    "type" => $type,
                ];
            }
            if ($x != '') {
                $label = $x . "Seeking Change";
                $type = strtoupper(str_replace(' ', '_', $label));
                $res[] = [
                    "value" => (float)$data->X,
                    "label" => $label,
                    "type" => $type,
                ];
            }
        }
        return $res;
    }

    public function getBiasName($value)
    {
        $res = '';
        if ($value <= 3) {
            $res = "Polar Low ";
        }
        if ($value >= 12) {
            $res = "Polar High ";
        }
        return $res;
    }

    public function countStudentUnderRound($year, $type, $bias, $polar, $round, $gender)
    {
        $t = $type == "IN_SCHOOL" ? "sch" : "at";
        $query = Model_ass_main::year($year)
            ->select('ass_rawdata.ref', 'ass_main_' . $year . '.assessment_sid as sid', 'ass_main_' . $year . '.round as round', 'ass_main_' . $year . '.pupil_id as pop_id')
            ->leftjoin('population', 'population.id', '=', 'ass_main_' . $year . '.pupil_id')
            ->leftjoin('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->leftjoin('ass_tracking_' . $year . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_rawdata.id')
            ->where('ass_main_' . $year . '.round', $round)
            ->where('ass_rawdata.type', $t)
            ->where('population.gender', $gender)
            ->where('population.level', 1)
            ->where('ass_main_' . $year . '.is_completed', 'Y');
        $data = $query->get();
        $count = 0;
        foreach ($data as $row) {
            $ref = substr($row->ref, 0, 19);
            $checkAssScore = Model_ass_score::year($year)
                ->where('ref', 'LIKE', $ref . '%')
                ->where('type', $t)
                ->where('sid', $row->sid)
                ->where('pop_id', $row->pop_id)
                ->first();
            if ($checkAssScore) {
                if ($polar == 'high' && $checkAssScore->{$bias}  >= 12) {
                    $count += 1;
                }
                if ($polar == 'low' && $checkAssScore->{$bias} <= 3) {
                    $count += 1;
                }
            }
        }
        return $count;
    }
    public function fetchPupilAssessment($filter, $type, $pupil_id, $latest_assessment_id)
    {
        $academicyear = $filter['academic_year'][0];
        $query = $this->Assessment($filter, $type);
        $query = $query->where('ass_main_' . $academicyear . '.id', $latest_assessment_id);
        $query = $query->where('ass_main_' . $academicyear . '.pupil_id', $pupil_id);
        return $query->first();
    }

    public function getAnalyzedData($filter, $ass_main_id)
    {
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)->where('analyzed', 1)->where('ass_main_' . $academicyear . '.id', $ass_main_id);
        return $query->first();
    }

    public function fetchdataForSummary($filter, $type, $ass_main_id)
    {
        $academicyear = $filter['academic_year'][0];
        $query = $this->Assessment($filter, $type);
        $query = $query->whereNull('analyzed');
        $query = $query->where('ass_main_' . $academicyear . '.id', $ass_main_id);
        return $query->first();
    }



    public function getStudentDataAvailableInYear($filter, $pupil_id, $assessment_type)
    {
        $type = $assessment_type == 'IN_SCHOOL' ? ['hs', 'sch'] : ['at'];
        $query = $this->Assessment($filter, $type);
        $query = $query->where('ass_score.pop_id', $pupil_id);
        return $query->get()->toArray();
    }

    public static function getLastAssesmentId($dataArr) {
        $year = $dataArr["academic_year"];
        $user_id = $dataArr["user_id"];
//        $platform_type = $dataArr["platform_type"];

        $data = Model_ass_main::year($year)
                ->where('pupil_id', $user_id)
//            ->where('is_completed', 'Y')
//                ->where('platform', $platform_type)
                ->orderBy('started_date', 'DESC')
                ->limit(1)
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function getByLimit($size, $year, $last_processed_id)
    {
        return Model_ass_main::year($year)->where('is_completed', 'Y')->whereNull('analyzed')->where('id', '>', $last_processed_id)->limit($size)->get();
    }

    public function UpdateRiskColumn($assessment_type, $year, $risk_count, $risk_type, $ass_main_id)
    {

        if( $assessment_type == 'IN_SCHOOL' && $risk_type == 'polar' ) {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update(['in_school_polar_count' => $risk_count, 'analyzed' => 1 ]);
        }
        else if( $assessment_type == 'OUT_OF_SCHOOL' && $risk_type == 'polar' ) {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update(['out_of_school_polar_count' => $risk_count, 'analyzed' => 1 ]);
        }
        else if( $assessment_type == 'IN_SCHOOL' && $risk_type == 'composite' ) {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update(['in_school_composite_count' => $risk_count, 'analyzed' => 1 ]);
        }
        else if( $assessment_type == 'OUT_OF_SCHOOL' && $risk_type == 'composite' ) {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update(['out_of_school_composite_count' => $risk_count, 'analyzed' => 1 ]);
        }
        else if( $risk_type == 'priority' ) {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update(['priority_count' => 1, 'analyzed' => 1 ]);
        }
        else {
            return Model_ass_main::year($year)->where('id', $ass_main_id )->update([ 'analyzed' => 1 ]);
        }
        return true;
    }

    public function getDataForRagPage($request, $filter, $sort_by, $sort_variant, $keyword, $count = false, $school_impact = false, $order)
    {
        $sort_by = strtolower($sort_by);
        $sort_variant = strtolower($sort_variant);
        $order = strtolower($order);
        $type = $sort_variant == "in_school" ? ['sch', 'hs'] : ['at'];
        $page = $request->get('page') ?? 1;
        $size = $request->get('size') ?? 15;
        $biases = ['dl', 'dh', 'tsl', 'tsh', 'tol', 'toh', 'ecl', 'ech', 'sn', 'hv', 'sci', 'ha', 'or', 'blu', 'sdl', 'sdh', 'eci', 'tsi', 'toi'];
        $skip = ($page - 1) * $size;
        $year = $filter['academic_year'];
        $round = $filter['assessment_round'];
        $query = Model_ass_main::year($year)
            ->select(
                'ass_main_' . $year . '.in_school_composite_count as user_in_school_composite_count',
                'ass_main_' . $year . '.out_of_school_composite_count as user_out_of_school_composite_count',
                'ass_main_' . $year . '.in_school_polar_count as user_in_school_polar_count',
                'ass_main_' . $year . '.out_of_school_polar_count as user_out_of_school_polar_count',
                'population.firstname',
                'population.lastname',
                'population.enc_pop_id',
                'population.username',
                'population.level',
                'population.gender',
                'population.dob',
                'population.id'
            );

        if ($sort_by == 'self_disclosure' || $sort_by == 'trust_of_self' || $sort_by == 'trust_of_others' || $sort_by == 'seeking_change') {
            $query = $query->join('ass_rawdata_' . $year . ' as ass_rawdata', function ($join) use ($year, $type) {
                $join->on('ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id')
                    ->whereIn('ass_rawdata.type', $type);
            });
            $query = $query->addSelect(
                'ass_score.P AS sd',
                'ass_score.S AS tos',
                'ass_score.L As too',
                'ass_score.X As sc'
            );
            $query = $query->join('ass_score_' . $year . ' as ass_score', function ($join) use ($type) {
                $join->on('ass_score.id', '=', 'ass_rawdata.id')
                    ->whereIn('ass_score.type', $type);
            });
        }
        $query = $query->join('population', 'ass_main_' . $year . '.pupil_id', '=', 'population.id');
        $query = $query->leftjoin('arr_year_' . $year . ' as arr_year', 'ass_main_' . $year . '.pupil_id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->RagPageGenericFilter($query, $filter, $year);
        $query = $filterUpdate->RagPageArrYearFilter($query, $filter, $year);
        if ($count == false && ($sort_by != '' || $sort_variant != '' || $order != '')) {
            $query = $filterUpdate->RagPageSort($query, $year, $round, $sort_by, $sort_variant, $order);
        }
        $query = $filterUpdate->RagPageMandatory($query, $year, $round);
        if ($sort_by == 'self_disclosure' || $sort_by == 'trust_of_self' || $sort_by == 'trust_of_others' || $sort_by == 'seeking_change') {
            $query = $query->get(); 
            $data = [];
            $bias = biasNameShort($sort_by);
            if ($sort_by == 'self_disclosure') {
                if ($order == 'low') {
                    $data = $query->sortBy(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
                if ($order == 'high') {
                    $data = $query->sortByDesc(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
            }
            if ($sort_by == 'trust_of_self') {
                if ($order == 'low') {
                    $data = $query->sortBy(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
                if ($order == 'high') {
                    $data = $query->sortByDesc(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
            }
            if ($sort_by == 'trust_of_others') {
                if ($order == 'low') {
                    $data = $query->sortBy(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
                if ($order == 'high') {
                    $data = $query->sortByDesc(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
            }
            if ($sort_by == 'seeking_change') {
                if ($order == 'low') {
                    $data = $query->sortBy(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
                if ($order == 'high') {
                    $data = $query->sortByDesc(function ($item) use($bias) {
                        return (float)$item->{$bias};
                    });
                }
            }
            $array = is_array($data) ? $data : $data->toArray();
            $total = count($data);
            $starting_point = ($page * $size) - $size;
            usort($array, function ($a, $b) use ($bias, $order) {
                $first = (float)$a[$bias];
                $second = (float)$b[$bias];
                if (isset($first) && isset($second)) {
                    if ($order == 'low') {
                        return $first > $second ? 1 : -1;
                    }
                    if ($order == 'high') {
                        return $first > $second ? -1 : 1;
                    }
                }
                return -1;
            });
            $array = array_slice($array, $starting_point, $size, true);
            $res = new Paginator($array, $total, $size, $page, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            return $res;
        }
        if ($keyword != '') {
            $keywordArray = explode(" ", $keyword);
            if (isset($keywordArray[0]) && !isset($keywordArray[1])) {
                $query = $query->where('population.firstname', 'LIKE', '%' . $keywordArray[0] . '%')
                    ->orWhere('population.lastname', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('population.username', 'LIKE', '%' . $keyword . '%');
            }
            if (isset($keywordArray[0]) && isset($keywordArray[1])) {
                $query = $query->where('population.firstname', 'LIKE', '%' . $keywordArray[0] . '%')
                    ->where('population.lastname', 'LIKE', '%' . $keywordArray[1] . '%');
                $query = $query->orWhere('population.username', 'LIKE', '%' . $keywordArray[0] . '%')
                    ->orWhere('population.username', 'LIKE', '%' . $keywordArray[1] . '%');
            }
        }
        if ($count == true) {
            return $query->count();
        }
        if ($school_impact == true) {
            return $query->get();
        }
        if ($sort_by == '' || empty($sort_by)) {
            $query = $query->orderBy('population.firstname', 'asc');
        }
        $query = $query->skip($skip)->paginate($size);
        return $query;
    }

    public function getAllStudentDataForFactorBias($filter, $year, $round)
    {
        $query = Model_ass_main::year($year)
            ->select(
                'population.firstname',
                'population.lastname',
                'population.enc_pop_id',
                'population.username',
                'population.level',
                'population.gender',
                'population.dob',
                'population.id as student_id',
                'ass_main_'.$year.'.*'
            );
        $query = $query->join('population', 'ass_main_' . $year . '.pupil_id', '=', 'population.id');
        $query = $query->leftjoin('arr_year_' . $year . ' as arr_year', 'ass_main_' . $year . '.pupil_id', '=', 'arr_year.name_id');
        // $query = $query->leftjoin('ass_rawdata_' . $year . ' as ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id');
        // $query = $query->leftjoin('ass_score_' . $year . ' as ass_score', 'ass_rawdata.id', '=', 'ass_score.id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->GeneralFilter($query, $filter, $year);
        $query = $query->where('ass_main_' . $year . '.round', $round);
        $query = $query->where('ass_main_' . $year . '.is_completed', 'Y');
        return $query->get();
    }

    public function getAllStudentWithAssessment($filter, $type)
    {
        $query = $this->AssessmentV2($filter, $type, true );
        return $query->get();
    }


    public function getPupilDataForRagPage($pupil_ids, $filter, $typeArray)
    {
        $year = $filter['academic_year'];
        $round = $filter['assessment_round'];

        $query = Model_ass_main::year($year)
            ->select(
                'population.datecreated',
                'population.enc_pop_id',
                'population.onboard_status',
                'population.datemodified',
                'population.dob',
                'population.level',
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                'population.gender',
                'ass_main_' . $year . '.is_manipulated as is_manipulated',
                'ass_main_' . $year . '.speed as speed',
                'ass_main_' . $year . '.completed_date',
                'ass_main_' . $year . '.is_completed',
                'ass_main_' . $year . '.priority_count',
                'ass_main_' . $year . '.in_school_polar_count',
                'ass_main_' . $year . '.out_of_school_polar_count',
                'ass_main_' . $year . '.in_school_composite_count',
                'ass_main_' . $year . '.out_of_school_composite_count',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $year . '.round',
                'ass_rawdata.type',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16',
                'ass_tracking.start',
                'ass_tracking.end',
                'ass_tracking.qtrack'
            )
            ->addSelect(DB::raw('CONCAT(population.firstname, " ", population.lastname) as name'))
            ->addSelect(DB::raw('DATE_ADD(ass_main_' . $year . '.completed_date, INTERVAL 90 DAY) AS future_date'))

            ->join('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $year . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id');
        $query = $query->where('ass_main_' . $year . '.round', $round);
        $query = $query->whereIn('ass_score.type', $typeArray);
        $query = $query->whereIn('ass_score.pop_id', $pupil_ids);
        $query = $query->groupBy('ass_score.pop_id');
        $query = $query->get();
        return $query;
    }

    public function getSinglePupilDataForRagPage($pupil_id, $filter, $typeArray)
    {
        $year = $filter['academic_year'];
        $round = $filter['assessment_round'];
        $query = Model_ass_main::year($year)
            ->select(
                'population.datecreated',
                'population.enc_pop_id',
                'population.onboard_status',
                'population.datemodified',
                'population.dob',
                'population.level',
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                'population.gender',
                'ass_main_' . $year . '.is_manipulated as is_manipulated',
                'ass_main_' . $year . '.speed as speed',
                'ass_main_' . $year . '.completed_date',
                'ass_main_' . $year . '.is_completed',
                'ass_main_' . $year . '.priority_count',
                'ass_main_' . $year . '.in_school_polar_count',
                'ass_main_' . $year . '.out_of_school_polar_count',
                'ass_main_' . $year . '.in_school_composite_count',
                'ass_main_' . $year . '.out_of_school_composite_count',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $year . '.round',
                'ass_rawdata.type',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16',
                'ass_tracking.start',
                'ass_tracking.end',
                'ass_tracking.qtrack'
            )
            ->addSelect(DB::raw('CONCAT(population.firstname, " ", population.lastname) as name'))
            ->addSelect(DB::raw('DATE_ADD(ass_main_' . $year . '.completed_date, INTERVAL 90 DAY) AS future_date'))

            ->join('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $year . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id');
        $query = $query->where('ass_main_' . $year . '.round', $round);
        $query = $query->whereIn('ass_score.type', $typeArray);
        $query = $query->where('ass_score.pop_id', $pupil_id);
        $query = $query->groupBy('ass_score.pop_id');
        $query = $query->first();
        return $query;
    }


    public function pupilBiasScoreWithType($typeArray, $filter, $pupil_id, $bias)
    {
        $year = $filter['academic_year'];
        $round = $filter['assessment_round'];

        $query = Model_ass_main::year($year)
            ->select('ass_score.*') 
            ->join('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id');
        $query = $query->where('ass_main_' . $year . '.round', $round);
        $query = $query->whereIn('ass_score.type', $typeArray);
        $query = $query->where('ass_score.pop_id', $pupil_id);
        $query = $query->where('ass_main_' . $year . '.is_completed', 'Y');
        $query = $query->orderBy('ass_score.id', 'DESC');
        $query = $query->first();
        return $query;
    }

    public static function getLastCompletedAssesmentId($dataArr)
    {
        $year = $dataArr["academic_year"];
        $user_id = $dataArr["user_id"];
        //        $platform_type = $dataArr["platform_type"];

        $data = Model_ass_main::year($year)
            ->where('pupil_id', $user_id)
            ->where('is_completed', 'Y')
            //                ->where('platform', $platform_type)
            ->orderBy('started_date', 'DESC')
            ->limit(1)
            ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public static function getcustomLastAssessment($ass_data, $round)
    {
        $year = $ass_data["academic_year"];
        $user_id = $ass_data["user_id"];
        $assessment_sid = $ass_data["assessment_sid"];
        $platform_type = $ass_data["platform_type"];

        $data = Model_ass_main::year($year)
            ->where('pupil_id', $user_id)
            ->where('assessment_sid', $assessment_sid)
            ->where('platform', $platform_type)
            ->where('is_completed', 'N')
            ->where('round', $round)
            ->orderBy('started_date', 'DESC')
            ->limit(1)
            ->first();

        return $data;
    }

    public function checkYearAndRound($year, $round)
    {
        $data = Model_ass_main::year($year)
            ->where('round', $round)
            ->where('is_completed', 'Y')
            ->count();
        return $data;
    }

    public function getAllRounds($year)
    {
        $data = Model_ass_main::year($year)
            ->select('round')
            ->where('is_completed', 'Y')
            ->where('round', '>', 0)
            ->orderBy('round', 'ASC')
            ->distinct()
            ->get()
            ->toArray();
        return $data ?? false;
    }

    public function getFirstAssessmentOnRoundDate($round, $year)
    {
        $data = Model_ass_main::year($year)
            ->select('completed_date')
            ->where('is_completed', 'Y')
            ->where('round', $round)
            ->orderBy('id', 'ASC')
            ->first();
        return $data->completed_date ?? false;
    }

    public function assesmentcomplatepupilcount_groupdash($year, $round, $status, $pupils = '',$is_campus = '')
    {
        $data = Model_ass_main::year($year);
        if($is_campus!=''){
            $data = $data->join('arr_year_' . $year . ' as arr_year', 'ass_main_' . $year . '.pupil_id', '=', 'arr_year.name_id');
            $data = $data->distinct('arr_year.name_id');
            $data->where(array('field' => 'campus', 'value' => $is_campus));
        }
        $data = $data->join('population', 'ass_main_' . $year . '.pupil_id', '=', 'population.id');
        $data = $data->where('population.firstname', 'NOT LIKE', '%test%');
        $data = $data->where('population.firstname', 'NOT LIKE', "%Testpupil%");
        $data = $data->where('population.firstname', 'NOT LIKE', "%Testsenior%");
        $data = $data->where('population.firstname', 'NOT LIKE', "%Testjunior%");
        $data->whereIn('round', $round);
        $data->whereIn('is_completed', $status);
        if (isset($pupils) && $pupils != '')
            $data->whereIn('pupil_id', $pupils);
        $data = $data->get();
        $result = FALSE;
        if (!empty($data)) {
            $result = $data;
        }
        return $result;
    }

    public function postassesmentpupilcount_groupdash($year, $round, $pupils = '')
    {
        $data = Model_ass_main::year($year);
        $data->whereIn('round', $round);
        if (isset($pupils) && $pupils != '')
            $data->whereIn('pupil_id', $pupils);
        $data = $data->get();
        $result = FALSE;
        if (!empty($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getStudentAssScorePerYearAndRound($year, $round, $type){
        $data = Model_ass_main::year($year)
        ->select('ass_main_'.$year.'.*', 'ass_score.P', 'ass_score.X', 'ass_score.L', 'ass_score.S')
        ->join('ass_rawdata_' . $year .' AS ass_rawdata', 'ass_main_'.$year.'.id', '=', 'ass_rawdata.ass_main_id')
        ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
        ->where('ass_main_'.$year.'.round', $round)
        ->where('ass_main_'.$year.'.is_completed', 'Y')
        ->whereIn('ass_rawdata.type', $type)
        ->get();
        return $data;
    }

    public function getStudentAssScorePerYearAndRoundAndTypeSingle($id, $year, $round, $type)
    {
        $data = Model_ass_main::year($year)
            ->select('ass_score.P', 'ass_score.X', 'ass_score.L', 'ass_score.S')
            ->join('ass_rawdata_' . $year . ' AS ass_rawdata', 'ass_main_' . $year . '.id', '=', 'ass_rawdata.ass_main_id')
            ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->where('ass_main_' . $year . '.round', $round)
            ->where('ass_main_' . $year . '.is_completed', 'Y')
            ->whereIn('ass_rawdata.type', $type)
            ->where('ass_score.pop_id', $id)
            ->first();
        if($data) {
            return $data;
        }
        return false;
    }

    public function getStudentAssScorePerYearAndRoundAndGender($year, $round, $type, $gender)
    {
        $data = Model_ass_main::year($year)
        ->select('ass_main_'.$year.'.*', 'ass_score.P', 'ass_score.X', 'ass_score.L', 'ass_score.S', 'population.firstname', 'population.lastname', 'population.username')
        ->join('population', 'ass_main_'.$year.'.pupil_id', '=', 'population.id')
        ->join('ass_rawdata_' . $year .' AS ass_rawdata', 'ass_main_'.$year.'.id', '=', 'ass_rawdata.ass_main_id')
        ->join('ass_score_' . $year . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
        ->where('ass_main_'.$year.'.round', $round)
        ->where('ass_main_'.$year.'.is_completed', 'Y')
        ->whereIn('ass_rawdata.type', $type);
        if($gender == 'male'){
            $data = $data->whereIn('population.gender', ['M', 'm', 'Male', 'male']);
        }
        if($gender == 'female'){
            $data = $data->whereIn('population.gender', ['F', 'f', 'Female', 'female']);
        }
        $data = $data->get();
        return $data;
    }

    public function AssessmentV2($filter, $type, $need_historic_data = false )
    {
        $academicyear = $filter['academic_year'][0];
        $query = Model_ass_main::year($academicyear)
            ->select(
                'population.id as student_id',
                'population.firstname',
                'population.lastname',
                DB::raw("(SELECT value FROM arr_year_$academicyear WHERE field = 'name_code' AND arr_year_$academicyear.name_id = population.id) as name_code"),
                'arr_year.field',
                'arr_year.value',
                'ass_main_' . $academicyear . '.is_manipulated as is_manipulated',
                'ass_main_' . $academicyear . '.speed as speed',
                'ass_main_' . $academicyear . '.is_completed',
                'population.gender',
                'ass_score.*',
                'ass_rawdata.ass_main_id',
                'ass_main_' . $academicyear . '.round',
                'ass_rawdata.q01',
                'ass_rawdata.q02',
                'ass_rawdata.q03',
                'ass_rawdata.q04',
                'ass_rawdata.q05',
                'ass_rawdata.q06',
                'ass_rawdata.q07',
                'ass_rawdata.q08',
                'ass_rawdata.q09',
                'ass_rawdata.q10',
                'ass_rawdata.q11',
                'ass_rawdata.q12',
                'ass_rawdata.q13',
                'ass_rawdata.q14',
                'ass_rawdata.q15',
                'ass_rawdata.q16'
            )
            ->join('ass_rawdata_' . $academicyear . ' AS ass_rawdata', 'ass_rawdata.ass_main_id', '=', 'ass_main_' . $academicyear . '.id')
            ->join('ass_score_' . $academicyear . ' AS ass_score', 'ass_score.id', '=', 'ass_rawdata.id')
            ->join('ass_tracking_' . $academicyear . ' AS ass_tracking', 'ass_tracking.score_id', '=', 'ass_score.id')
            ->join('population', 'ass_score.pop_id', '=', 'population.id')
            ->join('arr_year_' . $academicyear . ' AS arr_year', 'population.id', '=', 'arr_year.name_id');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterArrYear($query, $filter);
        $query = $filterUpdate->FilterArrMain($query, $filter, $academicyear);
        $query = $filterUpdate->FilterArrMainSelfJoin($query, $filter, $academicyear, $need_historic_data );
        $query = $query->where('ass_main_' . $academicyear . '.is_completed', 'Y');
        $query = $query->whereIn('ass_rawdata.type', $type);
        $query = $query->where('population.level', 1);
        $query = $query->groupBy('ass_main_'.$academicyear.'.pupil_id');
        return $query;
    }

    public function getDataForRagPagev2($request, $filter, $sort_param, $ass_main_ids = [])
    {
        list(
            'sort_by' => $sort_by,
            'sort_variant' => $sort_variant,
            'order' => $order,
            'keyword' => $keyword,
            'type' => $type,
            'page' => $page,
            'size' => $size,
            'assessment_type' => $assessment_type
        ) = $sort_param;
        $skip = ($page - 1) * $size;
        $year = $filter['academic_year'][0];
        $round = $filter['assessment_round'][0];
        $query = $this->AssessmentV2($filter, $type);
        if (isset($filter['gender'])) {
            $query = $query->where('population.gender', $filter['gender']);
        }
        
        if (!empty($ass_main_ids))
            $query = $query->whereIn('ass_main_' . $year . '.id', $ass_main_ids);

        //$query = $query->addSelect('ass_main_'. $year.'.in_school_composite_count','ass_main_'. $year.'.out_of_school_composite_count', 'ass_main_'. $year.'.priority_count');
        $query = $query->addSelect(DB::raw("TIMEDIFF( ass_main_" . $year . ".completed_date, ass_main_" . $year . ".started_date ) AS time"), 'ass_main_' . $year . '.completed_date');
        $filterUpdate = new Filter();
        $query = $filterUpdate->FilterByKeyword($query, $keyword);
        $sortUpdate = new Sort();
        $query = $sortUpdate->SortByAtoZ($query, $sort_by);
        $query = $sortUpdate->sortByIncOrDecPolarBias( $query, $round, $year, $sort_by, $sort_variant );
        $query = $sortUpdate->SortByParam($query, $year, $round, $sort_by, $sort_variant, $order, $page, $size);
        $query = $sortUpdate->SortByFactorBias($query, $sort_by, $page, $size, $order);
        if ($assessment_type == $sort_variant)
            $query = $query->skip($skip)->paginate($size);
        else
            $query = $query->get();
        return $query;
    }

    public function getDataForRagPagePriorityCountv2($filter, $type, $userList)
    {
        $filterhistory = $filter['history'];
        $data = [];
        foreach ($filterhistory as $f) {
            $round = $f['round'];
            $year = $f['academic_year'];
            $filter['round'][0] = $round;
            $filter['academic_year'][0] = $year;
            $query = $this->Assessment($filter, $type);
            $query = $query->whereIn('ass_main_' . $year . '.pupil_id', $userList);
            $result = $query->get()->toArray();
            $data = array_merge($result, $data);
        }
        return $data;
    }

    public function getStudentAssessmentById($filter, $student_id)
    {
        $data = Model_ass_main::year($filter['academic_year'][0])
            ->select('completed_date')
            ->where('pupil_id', $student_id)
            ->where('is_completed', 'Y')
            ->where('round', $filter['round'][0])
            ->first();
        if($data) {
            return $data;
        }
        return [];
    }

    public function getAssessmentByPopID($year, $round, $id)
    {
        $query = Model_ass_main::year($year)->select('*')
            ->addSelect(DB::raw("TIMEDIFF(completed_date, started_date ) AS time"))
            ->where('pupil_id', $id)
            ->where('round', $round);
        return $query->first();
        
    }

    public function is_historic_data($academicyear,$round) {
        $actionPlanMeta = new ActionPlanMetaServiceProvider();
        $yearList = $actionPlanMeta->academicYearsList( request()->school_id );
        if( $round == 3 ) {
            if( in_array( ($academicyear + 1), $yearList ) ) {
                $next_academic_year = $academicyear + 1;
                $round = 1;
            }else {
                $next_academic_year = $academicyear;
                $round = $round + 1;
            }
        }else {
            $next_academic_year = $academicyear;
            $round = $round + 1;
        }
        $data = Model_ass_main::year($next_academic_year)->where( 'round', '>' ,$round)->first();
        if( $data )
            return true;
        return false;
    }

    public function studentWithAssessment($year) {
        return Model_ass_main::year($year)->pluck('pupil_id')->toArray();
    }

    public function getStudentCount( $filter, $type, $operationtype, $round ) {
        $filter['round'][0] = $round;
        $query = $this->Assessment( $filter, $type );
        if( $operationtype == 'priority' ) {
            $query = $query->addSelect(  DB::raw( "CASE
                WHEN ( 
                      ( ass_score.P <= 3 || ass_score.P  >= 12 ) && 
                      ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
                      ( ass_score.S <= 3 || ass_score.S  >= 12 ) &&
                      ( ( ass_score.P <= 3 || ass_score.P >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
                      ( ass_score.L <= 3 || ass_score.L  >= 12 ) &&
                      ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.P <= 3 || ass_score.P >= 12 ) || ( ass_score.X <= 3 || ass_score.X >= 12 )  ) ||
                      ( ass_score.X <= 3 || ass_score.X  >= 12 ) &&
                      ( ( ass_score.S <= 3 || ass_score.S >= 12 ) || (  ass_score.L <= 3 || ass_score.L >= 12 ) || ( ass_score.P <= 3 || ass_score.P >= 12 )  )

                    )
                THEN 1 ELSE 0
                    END AS P1"
            ) );
            $query = $query->having('P1', 1);
        }

        if( $operationtype == 'viewall' ) {
            return $query->get();
        }

        return $query->get()->count();
    }

    public function getDeactivatedRoundData( $round, $academic_year ) {
        return Model_ass_main::year( $academic_year )->where('round', $round)->get();
    }

    public function activateRoundForSharedData( $data, $academic_year, $ass_main_id  ) {
        $query = Model_ass_main::year( $academic_year )->where( [ 'id' => $ass_main_id ])->update($data);
        return true;
    }

}
