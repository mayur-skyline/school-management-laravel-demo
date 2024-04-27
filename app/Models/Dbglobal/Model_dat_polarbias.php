<?php

namespace App\Models\Dbglobal;

use Illuminate\Database\Eloquent\Model;

class Model_dat_polarbias extends Model
{
    public $timestamps = false;
    protected $table = 'dat_polarbias';
    public function polarbiasByYear($arr_data) {
        $name = $arr_data['name'];
        $year = $arr_data['year'];

        $data = Model_dat_polarbias::select('value')
                ->where('name', $name)
                ->where('year', $year)
                ->orderBy('id', 'DESC')
                ->first();

        $result = FALSE;
        if($data){
            $result = $data;
        }
        return $result;
    }
    public function checkPolarbiasByYear($conditions) {
        $data = Model_dat_polarbias::select('*')
                ->where($conditions)
                ->orderBy('id', 'DESC')
                ->get();
        $result = FALSE;
        if($data){
            $result = $data;
        }
        return $result;
    }
    public function updatePolarbias($conditions, $data) {
        if (Model_dat_polarbias::where($conditions)->update($data)) {
            return true;
        } else {
            return false;
        }
    }
    public function savePolarbias($data) {
        $save = new Model_dat_polarbias;
        
        $save->stamp = $data['stamp'];
        $save->name = $data['name'];
        $save->year = $data['year'];
        $save->value = $data['value'];
        if ($save->save()) {
            $result['status'] = true;
            $result['last_id'] = $save->id;
        } else {
            $result['status'] = false;
        }
        return $result;
    }
}
