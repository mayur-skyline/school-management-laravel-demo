<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;
use DB;

class Model_ass_session_for_browser extends Model {

    protected $connection = "schools";
    protected $table = "ass_session_for_browser";
    public $timestamps = false;

    public function insert_session($unique_code, $time) {
        $curr_time = date('Y-m-d H:i:s');
        $timezone = date('e');

        $session_store = new Model_ass_session_for_browser();
        $session_store->code = $unique_code;
        $session_store->timeout = $time;
        $session_store->created = $curr_time;
        $session_store->timezone = $timezone;

        if ($session_store->save()) {
            $tmp['status'] = true;
            $tmp['last_id'] = $session_store->id;
        } else {
            $tmp['status'] = false;
        }
        return $tmp;
    }

    public function getLastSetSession($user_enter_code) {
        $session = Model_ass_session_for_browser::where('code', $user_enter_code)
                ->orderBy("id", "DESC")
                ->limit(1)
                ->first();
        return $session;
    }

    public function isExistSessionCode($session_code) {
        $session = Model_ass_session_for_browser::where('code', $session_code)
                ->orderBy("id", "DESC")
                ->limit(1)
                ->first();
        return $session;
    }

    public function isExistSessionId($id) {
        $session = Model_ass_session_for_browser::where('id', $id)
                ->first();
        return $session;
    }
    
    public function UpdateSesssionCodeTime($id){
        $current_date = date("Y-m-d H:i:s");
        $update_data = array(
            'created' => $current_date,
        );
        $update_switch = Model_ass_session_for_browser::where('id', $id)
                ->update($update_data);
        return $update_switch;
    }

}
