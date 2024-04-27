<?php

namespace App\Models\Dbglobal;

use Illuminate\Database\Eloquent\Model;

class Model_dat_package_subscription extends Model {

    public $timestamps = false;
    protected $table = 'dat_package_subscription';

    public function getSubPackages($school_id) {
        $data = Model_dat_package_subscription::select('field','value')->where('school_id', $school_id)->get();
        return $data;
    }

    public function getSinglePackage($package) {
        $school_id = mySchoolId();
        $result = Model_dat_package_subscription::select('field')->where('school_id', $school_id)->where('field', $package)->first();
        if (isset($result) && !empty($result)) {
            return true;
        } else {
            return FALSE;
        }
    }

    public function getPackageValue($school_id) {
        $data = Model_dat_package_subscription::select('value')
                ->where('school_id', $school_id)
                ->where('field', 'package')
                ->first();

        return $data;
    }
    public function getPackageOptionValue($school_id, $packageOption) {
        $data = Model_dat_package_subscription::select('value')
                ->where('school_id', $school_id)
                ->where('field', $packageOption)
                ->first();

        return $data;
    }

    public function checkFootprint($school_id) {
        $result = Model_dat_package_subscription::where('school_id', $school_id)->where('field', 'menu_footprints')->where('value', 1)->first();
        if (isset($result) && !empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkUsteer($school_id) {
        $result = Model_dat_package_subscription::where('school_id', $school_id)->where('field', 'menu_usteer')->where('value', 1)->first();
        if (isset($result) && !empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function getPlatformMsgStaff($school_id) {
        return Model_dat_package_subscription::where('school_id', $school_id)->where( 'field', 'ast_platform_msg_staff' )->first();
    }

    public function getPackage($conditions) {
        $data = Model_dat_package_subscription::where($conditions)->first();
        return $data;
    }

}
