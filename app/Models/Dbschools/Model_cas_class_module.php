<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;

class Model_cas_class_module extends Model {

    public $timestamps = false;
    protected $connection = "schools";
    protected $table = 'cas_class_modules';

    public function deletePupil($condition) {
        $delete_data = Model_cas_class_module::where($condition)
                ->delete();
        return TRUE;
    }

}
