<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_ass_rawdata extends Model {

    protected $connection = "schools";
    protected $table = "ass_rawdata";
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

    /**    ----------- Demo query to access table -----------

     *   public function demoQuery($table_year, $user_id) {
     *      $data = Model_arr_year::year($table_year)
     *             ->where('name_id', $user_id)
     *             ->where('field', 'year')
     *             ->first();
     *
     *    $result = FALSE;
     *    if ($data) {
     *        $result = $data->value;
     *    }
     *   return $result;
     * }
     *
     *
     * @param type $year
     * @param type $id
     * @return type
     */
    public static function getRawDataAt($aas_scr) {
        $year = $aas_scr["year"];
        $pop_id = $aas_scr["pop_id"];
        $school_id = $aas_scr["school_id"];

        $data = Model_ass_rawdata::year($year)
                ->where('pop_id', $pop_id)
                ->where('type', 'at')
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public static function getRawDataSch($aas_scr) {
        $year = $aas_scr["year"];
        $pop_id = $aas_scr["pop_id"];
        $school_id = $aas_scr["school_id"];

        $data = Model_ass_rawdata::year($year)
                ->where('pop_id', $pop_id)
                ->whereIn('type', array('hs', 'sch'))
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function pupilRawAt($raw_at) {
        $year = $raw_at["year"];
        $start_end_date = $raw_at['start_end_date'];
        $pop_id = $raw_at['pop_id'];
        $month = $raw_at['month'];
        $type = $raw_at['type'];
        $school_id = $raw_at['school_id'];
        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where(function($q) use ($month) {
                    $q->whereIn(DB::raw('SUBSTRING(ref, -8, 2)'), $month);
                })
                ->where('pop_id', $pop_id)
                ->where('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();
        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $data;
    }

    public function pupilRawSch($raw_sch) {
        $year = $raw_sch["year"];
        $start_end_date = $raw_sch['start_end_date'];
        $month = $raw_sch['month'];
        $pop_id = $raw_sch['pop_id'];
        $type = $raw_sch['type'];
        $school_id = $raw_sch['school_id'];
        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where(function($q) use ($month) {
                    $q->whereIn(DB::raw('SUBSTRING(ref, -8, 2)'), $month);
                })
                ->where('pop_id', $pop_id)
                ->whereIn('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();
        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public static function checkAssForSameDay($pop_id, $curr, $academicyear) {

        $data = Model_ass_rawdata::year($academicyear)
                ->where('pop_id', $pop_id)
                ->where('datetime', 'like', '%' . $curr . '%')
                ->whereIn('type', array('hs', 'sch', 'at'))
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }

        return $result;
    }

    public function submitAssessment($qid, $sid, $user_id, $your_school, $session_code, $academicyear) {

        $save_data = new Model_ass_rawdata();
        $save_data->setYear($academicyear);
        $save_data->pop_id = $user_id;
        $save_data->school_id = $your_school;
        $save_data->sid = $sid;
        $save_data->qid = $qid;
        $save_data->session_code = $session_code;

        if ($save_data->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $save_data->id;
        } else {
            $tmp['status'] = false;
        }
        return $tmp;
    }

    public static function recordAssessmentEntry($school_id, $academic_year, $assessment_sid, $qidi, $user_id, $assessment_type, $datetime, $ref, $session_code, $ass_main_table_id) {

        $save_data = new Model_ass_rawdata();
        $save_data->setYear($academic_year);
        $save_data->sid = $assessment_sid;
        $save_data->qid = $qidi;
        $save_data->pop_id = $user_id;
        $save_data->type = $assessment_type;
        $save_data->school_id = $school_id;
        $save_data->datetime = $datetime;
        $save_data->ref = $ref;
        $save_data->session_code = $session_code;
        $save_data->ass_main_id = $ass_main_table_id;

        if ($save_data->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $save_data->id;
            $tmp['ref'] = $save_data->ref;
        } else {
            $tmp['status'] = false;
        }
        return $tmp;
    }

    public function saveAssessmentAnswer($q_num, $q_val, $last_insretid, $factor, $your_school, $ref, $created, $academicyear) {
        $data_array = array(
            $q_num => $q_val,
            'type' => $factor,
            'school_id' => $your_school,
            'datetime' => $created,
            'ref' => $ref,
        );
        $d1 = Model_ass_rawdata::year($academicyear)
                ->where('id', $last_insretid)
                ->update($data_array);
        return TRUE;
    }

    public function updateRawMainId($id, $latest_id, $academicyear) {
        $data = Model_ass_rawdata::year($academicyear)
                ->where('id', $id)
                ->update(['ass_main_id' => $latest_id]);
        return TRUE;
    }

    public function checkAssessment($id, $academicyear) {
        $data = Model_ass_rawdata::year($academicyear)
                ->where('id', $id)
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function assDelete($academicyear) {
        $curr = date('Ymd');
        $d1 = Model_ass_rawdata::year($academicyear)
                ->where('datetime', 'like', '%' . $curr . '%')
                ->delete();
        return TRUE;
    }

    public static function pupilRawDataAt($score_at) {
        $year = $score_at["year"];
        $start_end_date = $score_at['start_end_date'];
        $month = $score_at['month'];
        $pop_id = $score_at['pop_id'];
        $type = $score_at['type'];
        $school_id = $score_at['school_id'];

        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where(function($q) use ($month) {
                    $q->whereIn(DB::raw('SUBSTRING(ref, -8, 2)'), $month);
                })
                ->where('pop_id', $pop_id)
                ->where('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->offset(1)
                ->limit(1)
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $data;
    }

    public static function pupilRawDataSch($score_at) {
        $year = $score_at["year"];
        $start_end_date = $score_at['start_end_date'];
        $month = $score_at['month'];
        $pop_id = $score_at['pop_id'];
        $type = $score_at['type'];
        $school_id = $score_at['school_id'];

        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where(function($q) use ($month) {
                    $q->whereIn(DB::raw('SUBSTRING(ref, -8, 2)'), $month);
                })
                ->where('pop_id', $pop_id)
                ->whereIn('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->offset(1)
                ->limit(1)
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $data;
    }

    public function rawAtWithoutMonth($dataArr) {
        $year = $dataArr["year"];
        $start_end_date = $dataArr['start_end_date'];
        $pop_id = $dataArr['pop_id'];
        $type = $dataArr['type'];
        $school_id = $dataArr['school_id'];

        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where('pop_id', $pop_id)
                ->where('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $data;
    }

    public function rawSchWithoutMonth($dataArr) {
        $year = $dataArr["year"];
        $start_end_date = $dataArr['start_end_date'];
        $pop_id = $dataArr['pop_id'];
        $type = $dataArr['type'];
        $school_id = $dataArr['school_id'];

        $data = Model_ass_rawdata::year($year)
                ->where(function($query) use ($start_end_date) {
                    $query->whereBetween(DB::raw('SUBSTRING(ref,-12)'), $start_end_date);
                })
                ->where('pop_id', $pop_id)
                ->whereIn('type', $type)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $data;
    }

    public function deletePupil($academicyear, $condition) {
        $delete_data = Model_ass_rawdata::year($academicyear)
                ->where($condition)
                ->delete();
        return TRUE;
    }

    public function getIdsByAssmainId($academic_year, $ass_main_id) {
        $data = Model_ass_rawdata::year($academic_year)
                ->select("id")
                ->where("ass_main_id", $ass_main_id)
                ->get()
                ->toArray();

        return $data;
    }

    public function deleteRawdata($academic_year, $data) {
        $result = Model_ass_rawdata::year($academic_year)
                ->whereIn('id', $data)
                ->delete();

        return $result;
    }

    public function getSections($academic_year, $assessment_id, $user_id, $ass_main_id) {
        $data = Model_ass_rawdata::year($academic_year)
                ->select("*")
                ->where("sid", $assessment_id)
                ->where("pop_id", $user_id)
                ->where("ass_main_id", $ass_main_id)
                ->orderByRaw('qid','asc')
                ->get()
                ->toArray();

        if ($data && !empty($data)) {
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getRawdataBySection($academic_year, $assessment_id, $qid, $user_id, $ass_main_id) {
        $result = Model_ass_rawdata::year($academic_year)
                ->select("*")
                ->where("sid", $assessment_id)
                ->where("qid", $qid)
                ->where("pop_id", $user_id)
                ->where("ass_main_id", $ass_main_id)
                ->first();

        return $result;
    }

    public function saveUpdateRawdataAnswers($academic_year, $rawdata_id, $user_id, $assessment_sid, $que_num, $option_val) {
        $result = Model_ass_rawdata::year($academic_year)
                ->where('id', $rawdata_id)
                ->where('sid', $assessment_sid)
                ->where('pop_id', $user_id)
                ->update([$que_num => $option_val]);
        return TRUE;
    }

    public function getRawData($dataArr) {

        $order_by = "cast(id AS SIGNED) DESC";
        $year = $dataArr['year'];
        $pop_id = $dataArr['pop_id'];
        $school_id = $dataArr['school_id'];
        $special_at = $dataArr['special_at'];

        $type = $dataArr['type'];
        $month = $dataArr['month'];
        $academicyear = $dataArr['academicyear'];
        $academicyear_start = $dataArr['academicyear_start'];
        $academicyear_end = $dataArr['academicyear_end'];
        $academicyear_close = $dataArr['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);

        $data = Model_ass_rawdata::year($year);
        $data->select('*');
        $data->where(function($query) use ($start_end_date) {
            $query->whereBetween(DB::raw('datetime'), $start_end_date);
        });
        $data->where(function($query) use ($month) {
            $query->whereIn(DB::raw('SUBSTRING(datetime,5,2)'), $month);
        });
        if (isset($special_at) && !empty($special_at)) {
            $data->where($special_at);
        }
        $data->where('pop_id', $pop_id);
        $data->where('school_id', $school_id);
        $data->whereIn('type', $type);

        $data->orderByRaw($order_by);
        $data->limit(1);
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getRawDataId($dataArr) {
        $year = $dataArr['year'];
        $pop_id = $dataArr['pop_id'];
        $school_id = $dataArr['school_id'];
        $special_at = $dataArr['special_at'];

        $ass_main_id = $dataArr['ass_main_id'];
        $type = $dataArr['type'];
        $month = $dataArr['month'];
        $academicyear = $dataArr['academicyear'];
        $academicyear_start = $dataArr['academicyear_start'];
        $academicyear_end = $dataArr['academicyear_end'];
        $academicyear_close = $dataArr['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);



        $data = Model_ass_rawdata::year($year);
        $data->select('ass_rawdata_' . $year . '.*');
        $data->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id');
        $data->where(function($query) use ($year, $start_end_date) {
            $query->whereBetween(DB::raw('ass_rawdata_' . $year . '.datetime'), $start_end_date);
        });
        $data->where(function($query) use ($year, $month) {
            $query->whereIn(DB::raw('SUBSTRING(ass_rawdata_' . $year . '.datetime,5,2)'), $month);
        });
        if (isset($special_at) && !empty($special_at)) {
            $data->where($special_at);
        }
        $data->where('ass_rawdata_' . $year . '.pop_id', $pop_id);
        $data->where('ass_rawdata_' . $year . '.school_id', $school_id);
        $data->whereIn('ass_rawdata_' . $year . '.type', $type);
        $data->whereIn('ass_score_' . $year . '.type', $type);
        $data->where('ass_rawdata_' . $year . '.ass_main_id', $ass_main_id);
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function previousRawData($dataArr) {
        $year = $dataArr['year'];
        $pop_id = $dataArr['pop_id'];
        $school_id = $dataArr['school_id'];
        $special_at = $dataArr['special_at'];

        $ass_main_id = $dataArr['ass_main_id'];
        $type = $dataArr['type'];
        $month = $dataArr['month'];
        $academicyear = $dataArr['academicyear'];
        $academicyear_start = $dataArr['academicyear_start'];
        $academicyear_end = $dataArr['academicyear_end'];
        $academicyear_close = $dataArr['academicyear_close'];
        $start_date = $academicyear . $academicyear_start . '010000';
        $end_date = $academicyear_close . $academicyear_end . '312359';
        $start_end_date = array($start_date, $end_date);

        $data = Model_ass_rawdata::year($year);
        $data->select('ass_rawdata_' . $year . '.*');
        $data->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id');
        $data->where(function($query) use ($year, $start_end_date) {
            $query->whereBetween(DB::raw('ass_rawdata_' . $year . '.datetime'), $start_end_date);
        });
        $data->where(function($query) use ($year, $month) {
            $query->whereIn(DB::raw('SUBSTRING(ass_rawdata_' . $year . '.datetime,5,2)'), $month);
        });
        if (isset($special_at) && !empty($special_at)) {
            $data->where($special_at);
        }
        $data->where('ass_rawdata_' . $year . '.pop_id', $pop_id);
        $data->where('ass_rawdata_' . $year . '.school_id', $school_id);
        $data->whereIn('ass_rawdata_' . $year . '.type', $type);
        $data->whereIn('ass_score_' . $year . '.type', $type);
        $data->where('ass_rawdata_' . $year . '.ass_main_id', $ass_main_id);
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    function getRawScoreTrackData($condition) {
        $year = $condition['year'];
        $pop_id = $condition['pop_id'];
        $type = ['p', 'at', 'sch', 'hs'];
        $data = Model_ass_rawdata::year($year);
        $data->whereIn('type', $type);
        $data->where('pop_id', $pop_id);
        $data->orderBy('id', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    function getRawDataAssmentId($assement_conditions) {
        $year = $assement_conditions['year'];
        $assement_id = $assement_conditions['assement_id'];
        $type = ['at', 'sch', 'hs'];
        $query = Model_ass_rawdata::year($year);
        $query->where('ass_main_id', $assement_id);
        $query->whereIn('type', $type);
        $query->orderBy('id', 'DESC');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    function getRawScoreByAssmentId($condition) {
        $year = $condition['year'];
        $assement_id = $condition['assement_id_array'];
        if (isset($condition['month']) && !empty($condition['month'])) {
            $month = $condition['month'];
        }
        $start_date = $condition['start_date'];
        $end_date = $condition['end_date'];

        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $start_end_date = array($start_date, $end_date);
        }
        $type = $condition['type'];
        $query = Model_ass_rawdata::year($year);
        $query->select('ass_rawdata_' . $year . '.*', 'ass_score_' . $year . '.P', 'ass_score_' . $year . '.S', 'ass_score_' . $year . '.L', 'ass_score_' . $year . '.X');
        $query->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id');
        $query->whereIn('ass_rawdata_' . $year . '.type', $type);
        $query->whereIn('ass_rawdata_' . $year . '.ass_main_id', $assement_id);
        if (isset($month) && !empty($month)) {
            $query->where(function($q) use ($month, $year) {
                $q->whereIn(DB::raw('SUBSTRING(ass_rawdata_'. $year . '.ref, -8, 2)'), $month);
            });
        }
        if (isset($start_end_date) && !empty($start_end_date)) {
            $query->where(function($q) use ($start_end_date, $year) {
                $q->whereBetween(DB::raw('SUBSTRING(ass_rawdata_'. $year . '.ref, -12)'), $start_end_date);
            });
        }
        if(isset($condition['order_by']) && !empty($condition['order_by'])){
            $query->orderBy('id', 'ASC');
        } else{
            $query->orderBy('id', 'DESC');
        }
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getDistinctData($year, $conditions = array()) {
        $query = Model_ass_rawdata::year($year)
                ->select($conditions['select'])
                ->distinct($conditions['select']);
        if (isset($conditions['sid']) && !empty($conditions['sid'])) {
            $query->where('sid', $conditions['sid']);
        }
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getArrRawData($academic_year, $pop_id) {

        $data = Model_ass_rawdata::year($academic_year)
                ->where('pop_id', $pop_id)
                ->orderBy('id', 'ASC')
                ->get();

        return $data;
    }

    public function insertAssRwaData($data, $academicyear) {
        $save_data = new Model_ass_rawdata();
        $save_data->setYear($academicyear);
        $save_data->sid = $data['sid'];
        $save_data->qid = $data['qid'];
        $save_data->q01 = $data['q01'];
        $save_data->q02 = $data['q02'];
        $save_data->q03 = $data['q03'];
        $save_data->q04 = $data['q04'];
        $save_data->q05 = $data['q05'];
        $save_data->q06 = $data['q06'];
        $save_data->q07 = $data['q07'];
        $save_data->q08 = $data['q08'];
        $save_data->q09 = $data['q09'];
        $save_data->q10 = $data['q10'];
        $save_data->q11 = $data['q11'];
        $save_data->q12 = $data['q12'];
        $save_data->q13 = $data['q13'];
        $save_data->q14 = $data['q14'];
        $save_data->q15 = $data['q15'];
        $save_data->q16 = $data['q16'];
        $save_data->q17 = $data['q17'];
        $save_data->q18 = $data['q18'];
        $save_data->q19 = $data['q19'];
        $save_data->q20 = $data['q20'];
        $save_data->q21 = $data['q21'];
        $save_data->q22 = $data['q22'];
        $save_data->q23 = $data['q23'];
        $save_data->q24 = $data['q24'];
        $save_data->q25 = $data['q25'];
        $save_data->q26 = $data['q26'];
        $save_data->q27 = $data['q27'];
        $save_data->q28 = $data['q28'];
        $save_data->pop_id = $data['pop_id'];
        $save_data->type = $data['type'];
        $save_data->school_id = $data['school_id'];
        $save_data->datetime = $data['datetime'];
        $save_data->ref = $data['ref'];

        if ($save_data->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $save_data->id;
        } else {
            $tmp['status'] = false;
        }
        return $tmp;
    }

    public function getAllAssRawData($condition) {

        if (isset($condition['month']) && !empty($condition['month'])) {
            $month = $condition['month'];
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
        }
        if ((isset($academicyear_close) && !empty($academicyear_close)) && (isset($academicyear_end) && !empty($academicyear_end))) {
            $end_date = $academicyear_close . $academicyear_end . '312359';
        }
        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $start_end_date = array($start_date, $end_date);
        }
        if (isset($condition['ass_main_id']) && !empty($condition['ass_main_id'])) {
            $ass_main_id = $condition['ass_main_id'];
        }
//        if (isset($condition['month_from_below']) && !empty($condition['month_from_below'])) {
//            $month_from_below = $condition['month_from_below'];
//        }
//        if (isset($condition['year_from_below']) && !empty($condition['year_from_below'])) {
//            $year_from_below = $condition['year_from_below'];
//        }

        if (isset($condition['ass_raw_datetime']) && !empty($condition['ass_raw_datetime'])) {
            $ass_raw_datetime = $condition['ass_raw_datetime'];
        }
        $year = $condition['year'];
        $pupil_id = $condition['pupil_id'];
        $data = Model_ass_rawdata::year($year);
        if (isset($start_end_date) && !empty($start_end_date)) {
            $data->where(function($query) use ($start_end_date) {
                $query->whereBetween(DB::raw('datetime'), $start_end_date);
            });
        }
        if (isset($month) && !empty($month)) {
            $data->where(function($query) use ($month) {
                $query->whereIn(DB::raw('SUBSTRING(datetime,5,2)'), $month);
            });
        }
        $data->where('pop_id', $pupil_id);
        if (isset($ass_main_id) && !empty($ass_main_id)) {
            $data->whereNotIn('ass_main_id', $ass_main_id);
        }
        if ((isset($month_from_below) && !empty($month_from_below)) && (isset($year_from_below) && !empty($year_from_below))) {
            $data->where(function($query) use ($month_from_below,$year_from_below) {
                $query->where(DB::raw('SUBSTRING(datetime,5,2)'),'<=',$month_from_below);
                $query->where(DB::raw('SUBSTRING(datetime,1,4)'),'<=',$year_from_below);
            });
        }
        if (isset($ass_raw_datetime) && !empty($ass_raw_datetime)) {
            $data->where(DB::raw("STR_TO_DATE(datetime, '%Y%m')"), '<=', DB::raw("STR_TO_DATE($ass_raw_datetime, '%Y%m')"));
        }
        $data->orderBy('id', 'DESC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAss($year, $conditions = array()) {
        $query = Model_ass_rawdata::year($year)
                ->where('sid', $conditions['sid'])
                ->where('pop_id', $conditions['pop_id'])
                ->whereIn('type', $conditions['type']);
        if (isset($conditions['datetime'])) {
            $query->where('datetime', 'like', '%' . $conditions['datetime'] . '%');
        }
        if (isset($conditions['today_date']) && isset($conditions['today_date'])) {
            $query->where(DB::raw('SUBSTRING(ref,-12, 8)'), '<=', $conditions['today_date']);
            $query->where(DB::raw('SUBSTRING(ref,-12, 8)'), '>=', $conditions['dayBefore']);
        }
        $query->orderBy($conditions['orderBy'], 'ASC');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function countAllAssQid($year, $conditions = array()) {
        $query = Model_ass_rawdata::year($year)
                ->select('qid', DB::raw('count(*) AS num'))
                ->where('sid', $conditions['sid'])
                ->where('pop_id', $conditions['pop_id'])
                ->whereIn('type', $conditions['type']);
        if (isset($conditions['datetime'])) {
            $query->where('datetime', 'like', '%' . $conditions['datetime'] . '%');
        }
        $query->groupBy('qid');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getRawdataByRawID($year, $rawdata_id) {
        $data = Model_ass_rawdata::year($year)
                ->where('id', $rawdata_id)
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function allRecentAssessment($conditins) {
        $year = $conditins['year'];
        $pop_id = $conditins["pop_id"];
        $school_id = $conditins["school_id"];
        $sid = $conditins["sid"];
        $id = $conditins["id"];
        $data = Model_ass_rawdata::year($year)
                ->where('pop_id', $pop_id)
                ->where('school_id', $school_id)
                ->where('sid', '>=', $sid)
                ->where('id', '>=', $id)
                ->orderBy('id', 'ASC')
                ->get();
        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    function getRawDataAssmentAllId($assement_conditions) {
        $year = $assement_conditions['year'];
        $assement_id = $assement_conditions['pupil_id'];

        if (isset($assement_conditions['month']) && !empty($assement_conditions['month'])) {
            $month = $assement_conditions['month'];
        }
        $type = ['at', 'sch', 'hs'];
        if (isset($assement_conditions['type']) && !empty($assement_conditions['type'])) {
            $type = $assement_conditions['type'];
        }
        $query = Model_ass_rawdata::year($year);
        $query->whereIn('pop_id', $assement_id);
        $query->whereIn('type', $type);
        if (isset($month) && !empty($month)) {
            $query->where(function($q) use ($month, $year) {
                $q->whereIn(DB::raw('SUBSTRING(ref, -8, 2)'), $month);
            });
        }
        $query->groupBy('pop_id');
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }
    public static function latestAssment($aas_scr) {
        $year = $aas_scr["year"];
        $pop_id = $aas_scr["pop_id"];
        $school_id = $aas_scr["school_id"];

        $data = Model_ass_rawdata::year($year)
                ->where('pop_id', $pop_id)
                ->where('school_id', $school_id)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if ($data) {
            $result = $data;
        }
        return $result;
    }
    public function getPupilRawScore($dataArr) {
        $year = $dataArr['year'];
        $pop_id = $dataArr['pop_id'];
        $type = $dataArr['type'];
        $data = Model_ass_rawdata::year($year)
            ->select('ass_rawdata_' . $year . '.*', 'ass_score_' . $year . '.P', 'ass_score_' . $year . '.S', 'ass_score_' . $year . '.L', 'ass_score_' . $year . '.X')
            ->join('ass_score_' . $year, 'ass_rawdata_' . $year . '.id', '=', 'ass_score_' . $year . '.id')
            ->join('ass_main_' . $year, 'ass_rawdata_' . $year . '.ass_main_id', '=', 'ass_main_' . $year . '.id')
            ->whereIn('ass_rawdata_' . $year . '.pop_id', $pop_id)
            ->whereIn('ass_rawdata_' . $year . '.type', $type)
            ->orderBy('pop_id', 'ASC')
            ->get();
        $result = "";
        if ($data) {
            $result = $data;
        }
        return $result;
    }

    public function getAssRawDataOrderByTime($academicyear, $ass_main_id) {
        $data = Model_ass_rawdata::year($academicyear)
                ->where('ass_main_id', $ass_main_id)
                ->orderBy('datetime', 'ASC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getLastAssRawData($academicyear) {
        $data = Model_ass_rawdata::year($academicyear)
                ->orderBy('id', 'DESC');
        $data = $data->first();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAssRawDataForTransfer($academicyear,$pop_id,$datetime) {
//        DB::connection('schools')->enableQueryLog();
        $data = Model_ass_rawdata::year($academicyear)
                ->where('pop_id', $pop_id)
                ->where('datetime', $datetime)
                ->orderBy('datetime', 'ASC')
                ->orderBy('id', 'ASC');
        $data = $data->get();
//        $q = DB::connection('schools')->getQueryLog();
//        echo '<pre>';
//        print_r($q);
//        die;
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function storeAssEntryInAssRawTableWithID($academicyear,$data) {
        $saveData= Model_ass_rawdata::year($academicyear);
        $data = $saveData->insert($data);
        $result = FALSE;
        if ($data) {
            $result = TRUE;
        }
        return $result;
    }

    public function checkAssMainId($academicyear,$data) {
        $query = Model_ass_rawdata::year($academicyear);
        $query->whereIn('ass_main_id', $data);
        $data = $query->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getAllAssRawDataWithoutPupil($condition) {

        if (isset($condition['month']) && !empty($condition['month'])) {
            $month = $condition['month'];
        }
        if (isset($condition['academicyear_start']) && !empty($condition['academicyear_start'])) {
            $academicyear_start = $condition['academicyear_start'];
        }
        if (isset($condition['academicyear_close']) && !empty($condition['academicyear_close'])) {
            $academicyear_close = $condition['academicyear_close'];
        }
        $academicyear = '';
        if (isset($condition['academicyear']) && !empty($condition['academicyear'])) {
            $academicyear = $condition['academicyear'];
        }
        if ((isset($academicyear) && !empty($academicyear)) && (isset($academicyear_start) && !empty($academicyear_start))) {
            $start_date = $academicyear . $academicyear_start . '01000000';
        }
        $academicyear_end = '';
        if (isset($condition['academicyear_end']) && !empty($condition['academicyear_end'])) {
            $academicyear_end = $condition['academicyear_end'];
        }
        if ((isset($academicyear_close) && !empty($academicyear_close)) && (isset($academicyear_end) && !empty($academicyear_end))) {
            $end_date = $academicyear_close . $academicyear_end . '31235959';
        }
        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $start_end_date = array($start_date, $end_date);
        }
        if (isset($condition['ass_main_id']) && !empty($condition['ass_main_id'])) {
            $ass_main_id = $condition['ass_main_id'];
        }

        $year = $condition['year'];

        $data = Model_ass_rawdata::year($year);
        if (isset($start_end_date) && !empty($start_end_date)) {
            $data->where(function($query) use ($start_end_date) {
                $query->whereBetween(DB::raw('datetime'), $start_end_date);
            });
        }
        if (isset($month) && !empty($month)) {
            $data->where(function($query) use ($month) {
                $query->whereIn(DB::raw('SUBSTRING(datetime,5,2)'), $month);
            });
        }
        if (isset($ass_main_id) && !empty($ass_main_id)) {
            $data->whereIn('ass_main_id', $ass_main_id);
        }
        $data->orderBy('id', 'DESC');
        $data = $data->get();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function UpdateRawdata($academicyear, $id, $update_array) {
        $data = Model_ass_rawdata::year($academicyear)
                ->where('id', $id)
                ->update($update_array);
        return TRUE;
    }

    public function getAllAssRawDataWithPupilIds($condition) {

        if (isset($condition['month']) && !empty($condition['month'])) {
            $month = $condition['month'];
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
        }
        if ((isset($academicyear_close) && !empty($academicyear_close)) && (isset($academicyear_end) && !empty($academicyear_end))) {
            $end_date = $academicyear_close . $academicyear_end . '312359';
        }
        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $start_end_date = array($start_date, $end_date);
        }
        if (isset($condition['ass_main_id']) && !empty($condition['ass_main_id'])) {
            $ass_main_id = $condition['ass_main_id'];
        }

        if (isset($condition['ass_raw_datetime']) && !empty($condition['ass_raw_datetime'])) {
            $ass_raw_datetime = $condition['ass_raw_datetime'];
        }
        $year = $condition['year'];
        $pupil_id = $condition['pupil_id'];
        $data = Model_ass_rawdata::year($year);
        if (isset($start_end_date) && !empty($start_end_date)) {
            $data->where(function($query) use ($start_end_date) {
                $query->whereBetween(DB::raw('datetime'), $start_end_date);
            });
        }
        if (isset($month) && !empty($month)) {
            $data->where(function($query) use ($month) {
                $query->whereIn(DB::raw('SUBSTRING(datetime,5,2)'), $month);
            });
        }
        $data->whereIn('pop_id', $pupil_id);
        if (isset($ass_main_id) && !empty($ass_main_id)) {
            $data->whereNotIn('ass_main_id', $ass_main_id);
        }
        if ((isset($month_from_below) && !empty($month_from_below)) && (isset($year_from_below) && !empty($year_from_below))) {
            $data->where(function($query) use ($month_from_below,$year_from_below) {
                $query->where(DB::raw('SUBSTRING(datetime,5,2)'),'<=',$month_from_below);
                $query->where(DB::raw('SUBSTRING(datetime,1,4)'),'<=',$year_from_below);
            });
        }
        if (isset($ass_raw_datetime) && !empty($ass_raw_datetime)) {
            $data->where(DB::raw("STR_TO_DATE(datetime, '%Y%m')"), '<=', DB::raw("STR_TO_DATE($ass_raw_datetime, '%Y%m')"));
        }
        $data->orderBy('id', 'DESC');
        $data = $data->get()->toArray();
        $result = FALSE;
        if (isset($data)) {
            $result = $data;
        }
        return $result;
    }

    public function getPupilRawData($pupilData, $year) {
        $result = Model_ass_rawdata::year($year)
            ->where(['ass_main_id' => $pupilData->ass_main_id, 'pop_id' => $pupilData->pupil_id])
            ->get();
        return $result;
    }

    public function FetchAllRawData($year, $main_id) {
        $result = Model_ass_rawdata::year($year)->where('ass_main_id', '>=', $main_id)->limit(150)->get();
        return $result;
    }

    public function GetRawDataByMain($year, $ass_main_id, $pupil_id) {
        $result = Model_ass_rawdata::year($year)
                  ->select('ass_rawdata_' . $year .'.*')
                  ->where('ass_rawdata_' . $year .'.ass_main_id', $ass_main_id)
                  ->get();
        return $result;
    }
    
    public function GetLastRawDataByMain($year, $ass_main_id, $pupil_id) {
        $result = Model_ass_rawdata::year($year)
                ->where('pop_id', $pupil_id)
                ->where('ass_main_id', $ass_main_id)
                ->orderBy('id', 'desc')
                ->first();
        return $result;
    }


    public function addQuestion($academic_year, $column, $value, $id) {
        $store_data = Model_ass_rawdata::year($academic_year)->where('id', $id)->orderBy('id', 'desc')->first();
        if ($store_data) {
            $store_data->{$column} = $value;
            $store_data->update();
            return true;
        } else {
            return false;
        }
    }

    public function PupilLastAssessment($years, $type, $pupil_id) {
        $type = $type == 1 ? ['at'] : ['sch', 'hs'];
        foreach( array_reverse($years) as $year) {
            $query = Model_ass_rawdata::year($year);
            $query = $query->whereIn('type', $type);
            $query = $query->where('pop_id', $pupil_id);
            $data = $query->orderBy('id', 'DESC')->first();
            if($data)
                return $data;
        }

        return null;
    }
    public function getSchoolRawdata( $year, $ass_main_ids ) {
        return Model_ass_rawdata::year($year)->whereIn('ass_main_id', $ass_main_ids)->get();
    }


}
