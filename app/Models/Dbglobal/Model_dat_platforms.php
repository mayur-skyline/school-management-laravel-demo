<?php

namespace App\Models\Dbglobal;

use Illuminate\Database\Eloquent\Model;

class Model_dat_platforms extends Model {

    protected $table = 'dat_platforms';

    public function getAllActivePlatforms() {
        $data = Model_dat_platforms::where('is_deleted', 'N')
                ->get();

        if (isset($data) && !empty($data)) {
            return $data;
        } else {
            return FALSE;
        }
    }

}
