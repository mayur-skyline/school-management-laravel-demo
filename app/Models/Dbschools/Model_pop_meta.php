<?php

namespace App\Models\Dbschools;

use DB;
use Illuminate\Database\Eloquent\Model;

class Model_pop_meta extends Model {

    public $timestamps = false;
    protected $connection = "schools";
    protected $table = 'pop_meta';

    public function getIsTrained($conditions) {
        $data = Model_pop_meta::select('is_trained')
                        ->where($conditions)->first();
        if (isset($data) && !empty($data)) {
            return $data;
        } else {
            return FALSE;
        }
    }
    public function deletePupil($conditions) {
        $delete_data = Model_pop_meta::where($conditions)
                ->delete();
        return TRUE;
    }
    public function updateData($conditions, $data) {
        if (Model_pop_meta::where($conditions)->update($data)) {
            return true;
        } else {
            return false;
        }
    }
    public function addData($data) {
        $save = new Model_pop_meta;
        $save->user_id = $data['user_id'];
        $save->is_trained = $data['is_trained'];
        if ($save->save()) {
            $result['status'] = true;
            $result['last_id'] = $save->id;
        } else {
            $result['status'] = false;
        }
        return $result;
    }
    public function getSingleData($conditions, $select) {
        $data = Model_pop_meta::select($select)
                        ->where($conditions)->first();
        if (isset($data) && !empty($data)) {
            return $data;
        } else {
            return FALSE;
        }
    }
}
