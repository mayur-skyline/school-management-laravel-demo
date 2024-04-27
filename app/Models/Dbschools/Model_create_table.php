<?php

namespace App\Models\Dbschools;

use Illuminate\Database\Eloquent\Model;
use Schema;

class Model_create_table extends Model {

    protected $connection = "schools";

    public function arrTable($tablename) {

        $con = $this->connection;
        if (isset($tablename) && !empty($tablename)) {
            $crtTable = Schema::connection($con)->create($tablename, function($table) {
                $table->engine = 'InnoDB';

                $table->increments('id', '11');
                $table->integer('name_id', '11');
                $table->string('field', '50');
                $table->string('value', '50');
            });
        }
    }

    public function rawdataTable($tablename) {
        $con = $this->connection;
        if (isset($tablename) && !empty($tablename)) {
            $crtTable = Schema::connection($con)->create($tablename, function($table) {
                $table->engine = 'InnoDB';

                $table->increments('id', '11');
                $table->string('sid', '11');
                $table->string('qid', '11');
                $table->char('q01', '50')->default('');
                $table->char('q02', '50')->default('');
                $table->char('q03', '50')->default('');
                $table->char('q04', '50')->default('');
                $table->char('q05', '50')->default('');
                $table->char('q06', '50')->default('');
                $table->char('q07', '50')->default('');
                $table->char('q08', '50')->default('');
                $table->char('q09', '50')->default('');
                $table->char('q10', '50')->default('');
                $table->char('q11', '50')->default('');
                $table->char('q12', '50')->default('');
                $table->char('q13', '50')->default('');
                $table->char('q14', '50')->default('');
                $table->char('q15', '50')->default('');
                $table->char('q16', '50')->default('');
                $table->char('q17', '50')->default('');
                $table->char('q18', '50')->default('');
                $table->char('q19', '50')->default('');
                $table->char('q20', '50')->default('');
                $table->char('q21', '50')->default('');
                $table->char('q22', '50')->default('');
                $table->char('q23', '50')->default('');
                $table->char('q24', '50')->default('');
                $table->char('q25', '50')->default('');
                $table->char('q26', '50')->default('');
                $table->char('q27', '50')->default('');
                $table->string('pop_id', '11');
                $table->string('type', '35');
                $table->string('school_id', '11');
                $table->char('datetime', '12');
                $table->string('ref', '12');
                $table->string('session_code', '8');
            });
        }
    }

    public function scoreTable($tablename) {
        $con = $this->connection;
        if (isset($tablename) && !empty($tablename)) {
            $crtTable = Schema::connection($con)->create($tablename, function($table) {
                $table->engine = 'InnoDB';

                $table->increments('id', '11');
                $table->string('sid', '11');
                $table->string('qid', '50');
                $table->char('P', '5')->default('');
                $table->char('R', '5')->default('');
                $table->char('S', '5');
                $table->char('W', '5');
                $table->char('X', '5');
                $table->char('C', '5');
                $table->char('L', '5');
                $table->char('N', '5');
                $table->char('M', '5')->default('');
                $table->char('V', '5')->default('');
                $table->char('O', '5')->default('');
                $table->char('F', '5')->default('');
                $table->char('T', '5')->default('');
                $table->char('PR', '5')->default('');
                $table->string('pop_id', '11');
                $table->string('type', '35');
                $table->string('school_id', '11');
                $table->char('datetime', '12');
                $table->string('ref', '70');
            });
        }
    }

    public function trackingTable($tablename) {
        $con = $this->connection;
        if (isset($tablename) && !empty($tablename)) {
            $crtTable = Schema::connection($con)->create($tablename, function($table) {
                $table->engine = 'InnoDB';

                $table->increments('id', '11');
                $table->string('sid', '11');
                $table->string('qid', '11');
                $table->string('score_id', '50');
                $table->string('pop_id', '50');
                $table->char('start', '20');
                $table->char('end', '20');
                $table->longText('qtrack');
                $table->char('gender', '5');
                $table->string('school_year', '2');
                $table->string('academic_year', '4');
                $table->string('type', '35');
                $table->string('ref', '70');
            });
        }
    }
    
    public function mainTable($tablename) { 
        $con = $this->connection;
        if (isset($tablename) && !empty($tablename)) {
            $crtTable = Schema::connection($con)->create($tablename, function($table) {
                $table->engine = 'InnoDB';

                $table->increments('id', '11');
                $table->string('pupil_id', '11');
                $table->string('assessment_sid', '11');
                $table->enum('is_completed', ['Y', 'N']);
                $table->char('started_date', '20');
                $table->char('completed_date', '20');
                $table->enum('platform', ['1', '2', '3', '4', '5', '6', '7', '8', '9','10','11','12','13','14','15']);
            });
        }
    }
}
