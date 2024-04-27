<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Dbschools\Model_arr_year;

class AstCommonServiceProvider {
     public function __construct(){
         $this->arrYear_model = new Model_arr_year();
     }

    public function getRawDataAtCnt($qdraw) {
//        $qdraw = $this->assRawData_model->getRawDataAt($aas_scr);
        $raw = array();
        if (isset($qdraw) && $qdraw != FALSE) {
            if ($qdraw['q01'] != "")
                $raw[] = $qdraw['q01'];
            if ($qdraw['q02'] != "")
                $raw[] = $qdraw['q02'];
            if ($qdraw['q03'] != "")
                $raw[] = $qdraw['q03'];
            if ($qdraw['q04'] != "")
                $raw[] = $qdraw['q04'];
            if ($qdraw['q05'] != "")
                $raw[] = $qdraw['q05'];
            if ($qdraw['q06'] != "")
                $raw[] = $qdraw['q06'];
            if ($qdraw['q07'] != "")
                $raw[] = $qdraw['q07'];
            if ($qdraw['q08'] != "")
                $raw[] = $qdraw['q08'];
            if ($qdraw['q09'] != "")
                $raw[] = $qdraw['q09'];
            if ($qdraw['q10'] != "")
                $raw[] = $qdraw['q10'];
            if ($qdraw['q11'] != "")
                $raw[] = $qdraw['q11'];
            if ($qdraw['q12'] != "")
                $raw[] = $qdraw['q12'];
            if ($qdraw['q13'] != "")
                $raw[] = $qdraw['q13'];
            if ($qdraw['q14'] != "")
                $raw[] = $qdraw['q14'];
            if ($qdraw['q15'] != "")
                $raw[] = $qdraw['q15'];
            if ($qdraw['q16'] != "")
                $raw[] = $qdraw['q16'];
            if ($qdraw['q17'] != "")
                $raw[] = $qdraw['q17'];
            if ($qdraw['q18'] != "")
                $raw[] = $qdraw['q18'];
            if ($qdraw['q19'] != "")
                $raw[] = $qdraw['q19'];
            if ($qdraw['q20'] != "")
                $raw[] = $qdraw['q20'];
            if ($qdraw['q21'] != "")
                $raw[] = $qdraw['q21'];
            if ($qdraw['q22'] != "")
                $raw[] = $qdraw['q22'];
            if ($qdraw['q23'] != "")
                $raw[] = $qdraw['q23'];
            if ($qdraw['q24'] != "")
                $raw[] = $qdraw['q24'];
            if ($qdraw['q25'] != "")
                $raw[] = $qdraw['q25'];
            if ($qdraw['q26'] != "")
                $raw[] = $qdraw['q26'];
            if ($qdraw['q27'] != "")
                $raw[] = $qdraw['q27'];
            if ($qdraw['q28'] != "")
                $raw[] = $qdraw['q28'];
        }
        return $raw;
    }

    public function getRawDataSchCnt($qdrawhs) {
//        $qdrawhs = $this->assRawData_model->getRawDataSch($aas_scr);
        $rawc = array();
        if (isset($qdrawhs) && $qdrawhs != FALSE) {
            if ($qdrawhs['q01'] != "")
                $rawc[] = $qdrawhs['q01'];
            if ($qdrawhs['q02'] != "")
                $rawc[] = $qdrawhs['q02'];
            if ($qdrawhs['q03'] != "")
                $rawc[] = $qdrawhs['q03'];
            if ($qdrawhs['q04'] != "")
                $rawc[] = $qdrawhs['q04'];
            if ($qdrawhs['q05'] != "")
                $rawc[] = $qdrawhs['q05'];
            if ($qdrawhs['q06'] != "")
                $rawc[] = $qdrawhs['q06'];
            if ($qdrawhs['q07'] != "")
                $rawc[] = $qdrawhs['q07'];
            if ($qdrawhs['q08'] != "")
                $rawc[] = $qdrawhs['q08'];
            if ($qdrawhs['q09'] != "")
                $rawc[] = $qdrawhs['q09'];
            if ($qdrawhs['q10'] != "")
                $rawc[] = $qdrawhs['q10'];
            if ($qdrawhs['q11'] != "")
                $rawc[] = $qdrawhs['q11'];
            if ($qdrawhs['q12'] != "")
                $rawc[] = $qdrawhs['q12'];
            if ($qdrawhs['q13'] != "")
                $rawc[] = $qdrawhs['q13'];
            if ($qdrawhs['q14'] != "")
                $rawc[] = $qdrawhs['q14'];
            if ($qdrawhs['q15'] != "")
                $rawc[] = $qdrawhs['q15'];
            if ($qdrawhs['q16'] != "")
                $rawc[] = $qdrawhs['q16'];
            if ($qdrawhs['q17'] != "")
                $rawc[] = $qdrawhs['q17'];
            if ($qdrawhs['q18'] != "")
                $rawc[] = $qdrawhs['q18'];
            if ($qdrawhs['q19'] != "")
                $rawc[] = $qdrawhs['q19'];
            if ($qdrawhs['q20'] != "")
                $rawc[] = $qdrawhs['q20'];
            if ($qdrawhs['q21'] != "")
                $rawc[] = $qdrawhs['q21'];
            if ($qdrawhs['q22'] != "")
                $rawc[] = $qdrawhs['q22'];
            if ($qdrawhs['q23'] != "")
                $rawc[] = $qdrawhs['q23'];
            if ($qdrawhs['q24'] != "")
                $rawc[] = $qdrawhs['q24'];
            if ($qdrawhs['q25'] != "")
                $rawc[] = $qdrawhs['q25'];
            if ($qdrawhs['q26'] != "")
                $rawc[] = $qdrawhs['q26'];
            if ($qdrawhs['q27'] != "")
                $rawc[] = $qdrawhs['q27'];
            if ($qdrawhs['q28'] != "")
                $rawc[] = $qdrawhs['q28'];
        }
        return $rawc;
    }

    public function bothRawData($qdraw, $qdrawhs) {
        // get
        $raw = $this->getRawDataAtCnt($qdraw);

        $raw_gen = array();
        if ($raw != null) {
            $raw_gen = $raw;
            $raw_gen_sum = array_sum($raw_gen);
            if (count($raw_gen) > 0)
                $raw_gen_mean = $raw_gen_sum / count($raw_gen);
            else
                $raw_gen_mean = 0;
            $raw_gen_variance = 0.0;
            foreach ($raw_gen as $raw_gen_i) {
                $raw_gen_variance += pow($raw_gen_i - $raw_gen_mean, 2);
            }
            if (count($raw_gen) > 0)
                $raw_gen_variance /= ( false ? count($raw_gen) - 1 : count($raw_gen) );
            else
                $raw_gen_variance = 0;
        }

        // con
        $rawc = $this->getRawDataSchCnt($qdrawhs);
        $raw_con = array();
        if ($rawc != "") {
            $raw_con = $rawc;
            $raw_con_sum = array_sum($raw_con);
            if (count($raw_con) > 0)
                $raw_con_mean = $raw_con_sum / count($raw_con);
            else
                $raw_con_mean = 0;
            $raw_con_variance = 0.0;
            foreach ($raw_con as $raw_con_i) {
                $raw_con_variance += pow($raw_con_i - $raw_con_mean, 2);
            }
            if (count($raw_con) > 0)
                $raw_con_variance /= ( false ? count($raw_con) - 1 : count($raw_con) );
            else
                $raw_con_variance = 0;
        }

        // Both
        $raw_both = array_merge($raw_gen, $raw_con);
        $raw_both_sum = array_sum($raw_both);
        if (count($raw_both) > 0)
            $raw_both_mean = $raw_both_sum / count($raw_both);
        else
            $raw_both_mean = 0;

        $raw_both_variance = 0.0;
        foreach ($raw_both as $raw_both_i) {
            $raw_both_variance += pow($raw_both_i - $raw_both_mean, 2);
        }
        if (count($raw_both) > 0)
            $raw_both_variance /= ( false ? count($raw_both) - 1 : count($raw_both) );
        else
            $raw_both_variance = 0;

        $raw_meam = array(
            "raw_both_mean" => $raw_both_mean,
            "raw_both_variance" => $raw_both_variance,
        );
        return $raw_meam;
    }

    public function titleFilter($fields_array, $year, $school_id) {
        for ($i = 0; $i < count($fields_array); $i++) {
            $field_name = $fields_array[$i];
            $forWidget = array();

            $field_arr = array(
                'year' => $year,
                'school_id' => $school_id,
                'field' => $field_name,
            );
            $field_data = $this->arrYear_model->getArrData($field_arr);
            if (isset($field_data) && $field_data != FALSE) {
                foreach ($field_data as $f_data) {
                    $forWidget[] = trim($f_data['value']);
                }
                $titleArr[$field_name] = $forWidget;
            }
        }
        return $titleArr;
    }

}
