<?php

namespace App\Models\Dbglobal;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Encdec;

class Model_dat_population extends Model {

    public $timestamps = false;
    protected $table = 'dat_population';

    public function __construct() {
        // Initialize libraries
        $this->encdec = new Encdec();
    }

    public function checkUsernameExist($username) {
        $is_username = Model_dat_population::where('username', $username)->first();
        return $is_username;
    }

    public function getUsername($school_id, $user_id) {
        $user_detail = Model_dat_population::where('school_id', $school_id)
                ->where('local_id', $user_id)
                ->first();

        $result = FALSE;
        if ($user_detail) {
            $result = $user_detail;
        }
        return $result;
    }

    public function checkPupilInGlobal($data) {
        $username = $data["username"];
        $password = $data["password"];

        $pwd_length = strlen($password);

        $user_detail = Model_dat_population::where('username', $username)
                ->where('password', $password)
                ->first();
        $status = FALSE;

        if (empty($user_detail)) { // Not found. So check if is the password is in encryptef format?
            $enc_password = $this->encdec->enc_password($password);

            $user_detail = Model_dat_population::where('username', $username)
                    ->where('password', $enc_password)
                    ->first();

            if (isset($user_detail) && !empty($user_detail)) { // Password were stored in encrypted format in database.
                $status = TRUE;
            }
        } else { // it means the password is not in encrypted format in database so need to be encrypt.
            $status = TRUE;
            $user_detail["needToUpdate"] = TRUE;
            $user_detail["updated_password"] = $this->encdec->enc_password($password);
        }
        if (isset($status) && $status == TRUE) {
            return $user_detail;
        } else {
            $this->encdec->enc_dummy_password(); // Prevent the timing attack...
            return FALSE;
        }
    }

    public function checkStaffInGlobal($data) {
        $username = $data["username"];
        $password = $data["password"];

        $user_detail = Model_dat_population::where('username', $username)->first();
        $status = FALSE;

        if (isset($user_detail) && !empty($user_detail)) { // User found
            $dbpwd = $user_detail->password;

            $dbpwd_length = strlen($dbpwd);

            if ($dbpwd == $password) {

                $status = TRUE;
                $user_detail["needToUpdate"] = TRUE;
                $user_detail["updated_password"] = $this->encdec->plainTohashPassword($password);
            } else {
                $pwd_data = array(
                    "user_inputed_password" => $password,
                    "check_against_password" => $dbpwd,
                );

                $is_match = $this->encdec->matchHashWithPlainPassword($pwd_data);

                if (isset($is_match) && $is_match == TRUE) {
                    $status = TRUE;
                    $needsRehashPassword = $this->encdec->needsRehashPassword($pwd_data); // Check does hashing needs re-hashing?

                    if (isset($needsRehashPassword) && $needsRehashPassword == TRUE) {
                        $user_detail["needToUpdate"] = TRUE;
                        $user_detail["updated_password"] = $this->encdec->plainTohashPassword($password);
                    }
                }
                unset($pwd_data);
            }
        }
        if (isset($status) && $status == TRUE) {
            return $user_detail;
        } else {
            $this->encdec->checkAgainstDummyHash(); // Prevent the timing attack...
            return FALSE;
        }
    }

    public function updateGlobalPassword($data) {
        $user_id = $data["local_id"];
        $school_id = $data["school_id"];
        $new_updated_password = $data["new_updated_password"];

        $is_update = Model_dat_population::where('local_id', $user_id)
                ->where('school_id', $school_id)
                ->update(['password' => $new_updated_password, 'datemodified' => date("Y-m-d h:i:s")]);

        if ($is_update) {
            return true;
        } else {
            return false;
        }
    }

    public function saveNewWondeStaffData($newimportdata, $local_id) {
        $savenewimport = new Model_dat_population;
        unset($savenewimport['encdec']);
        $savenewimport->local_id = $local_id;
        $savenewimport->school_id = $newimportdata['school_id'];
        $savenewimport->username = $newimportdata['username'];
        $savenewimport->password = $newimportdata['password'];
        $savenewimport->datecreated = $newimportdata['datecreated'];
        $savenewimport->user_token = "";


        if ($savenewimport->save()) {
            $result['status'] = true;
        } else {
            $result['status'] = false;
        }
        return $result;
    }

    public function updateNewWondeStaffData($staffdata, $auto_id) {
        if (isset($staffdata['password']) && $staffdata['password'] != "") {
            $updatearray = ['school_id' => $staffdata['school_id'],
                'username' => $staffdata['username'],
                'password' => $staffdata['password'],
                'datemodified' => $staffdata['datecreated']];
        } else {
            $updatearray = ['school_id' => $staffdata['school_id'],
                'username' => $staffdata['username'],
                'datemodified' => $staffdata['datecreated']];
        }

        $updatedata = Model_dat_population::
                where('local_id', $auto_id)->
                where('school_id', $staffdata['school_id'])->
                update($updatearray);

        return $updatedata;
    }

    public function deleteData($conditions) {
        $delete = Model_dat_population::where($conditions)->delete();
        return TRUE;
    }

    public function updateDatStaffData($conditions, $data) {
        if (Model_dat_population::where($conditions)->update($data)) {
            return true;
        } else {
            return false;
        }
    }

}
