<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;

class Model_blog_share extends Model {

    public $timestamps = false;
    protected $connection = "schools";
    protected $table = "blog_share";

    public function deleteData($condition) {
        $delete_data = Model_blog_share::where($condition)
                ->delete();
        return TRUE;
    }

}
