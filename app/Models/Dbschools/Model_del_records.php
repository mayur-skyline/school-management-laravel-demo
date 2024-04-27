<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Model_del_records extends Model {

    protected $connection = 'schools';
    protected $table = 'ass_del_record';
    public $timestamps = false;

    public function savedel_record($data) {
        $save_data = Model_del_records::insertGetId($data);
        return $save_data;
    }

    public function getDeletedList($type) {
        if($type=='school') {
            $ddrepl = Model_del_records::
                    select('*')
                    ->where('type', $type)
                    ->orderby('id', 'DESC')
                    ->get();
            $result = FALSE;
            if ($ddrepl) {
                $result = $ddrepl;
            }
        }else{
            $ddrepl = Model_del_records::
                    select('*')
                    ->where('campus', $type)
                    ->orderby('id', 'DESC')
                    ->get();
            $result = FALSE;
            if ($ddrepl) {
                $result = $ddrepl;
            }
        }
        return $result;
    }


}
