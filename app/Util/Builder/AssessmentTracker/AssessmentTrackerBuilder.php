<?php

namespace App\Util\Builder\AssessmentTracker;
use App\Util\Builder\AssessmentTracker\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AssessmentTrackerBuilder extends Builder
{
    public function studentdetail(object $student, bool $name_code_schl, array $user_code, Collection $student_properties ): Array
    {
        return array(
            'id' => $student->id,
            'mis_id' => $student->mis_id,
            'name' => $student->firstname . ' ' . $student->lastname,
            'gender' => $student->gender, 
            'username' => $student->username,
            'password' => $student->password,
            'name_code' =>  $name_code_schl == true ? ( $student->firstname . ' ' . $student->lastname ) : $user_code[$student->id] ?? getUserNameCode( $student ),
            'tutor' => $student_properties[ $student->id.'-form_teacher' ][0]['value'] ?? null,
            'tutor_group' => $student_properties[ $student->id.'-form_set'][0]['value'] ?? null,
            'year_group' => $student_properties[ $student->id.'-year'][0]['value'] ?? null
        );
    }

    public function questionAnswerbuilder2($pupildata, $assessment_type, $questions, $type): ?Array
    {
        $ass_type = $assessment_type == 'IN_SCHOOL' ? [ 'hs', 'sch' ] : [ 'at' ];

        if( $type == 'NOT_STARTED' ) 
            return null; 

        if( count($pupildata['rawdata']) == 0 )
            return null;

        if( in_array( $pupildata['rawdata'][0]['type'], $ass_type ) )
            $rawdata = $pupildata['rawdata'][0] ?? null;
        else 
            $rawdata = $pupildata['rawdata'][1] ?? null;
        
        if( $rawdata == null ) 
            return null;


        if( $questions == null )
            return null;

        for ($i = 1; $i <= 16; $i++) {
                if ($i < 10) {
                    $ans_value =  $rawdata->{ "q0" . $i};
                    $ans = $this->getAnswer($i, $questions, $rawdata, $ans_value);
                    $que =$this->getQuestion($questions, $rawdata, $ass_type, $i);
                    if( $ans && $que ) {

                        $variant[] = array(
                            'question' => $questions[$rawdata->sid][$rawdata->qid]["q".$i],
                            'answer' => $ans,
                            'score' =>  $ans_value != "" ? (int)$ans_value : null ,
                            'time' => $this->CalculateTime($rawdata, $i, $type)
                        );
                    }
                }
                    
                else if ($i <= 16) {
                    $ans = $this->getAnswer($i, $questions, $rawdata, $rawdata->{"q" . $i});
                    $ans_value =  $rawdata->{"q" . $i};
                    $que =$this->getQuestion( $questions, $rawdata, $ass_type, $i);
                    if( $ans && $que ) {
                        
                        $variant[] = array(
                            'question' => $questions[$rawdata->sid][$rawdata->qid]["q".$i],
                            'answer' => $ans,
                            'score' => $ans_value != "" ? (int)$ans_value : null,
                            'time' =>  $this->CalculateTime($rawdata, $i, $type)
                        );
                    }
                }
        }
        
        if( !isset($variant) ) {
            return null;
        }
        return $variant;
    }

    public function questionAnswerbuilder(object $rawdata, array $questions, string $type, string $assesment_type): Array
    {
        $variant = [];
        $ass_type = $assesment_type == 'IN_SCHOOL' ? 'in' : 'out';
        for ($i = 1; $i <= 16; $i++) {
                if ($i < 10) {
                    $ans_value =  $rawdata->{ $ass_type."_q0" . $i};
                    $ans = $this->getAnswer($i, $questions, $rawdata, $ass_type, $ans_value);
                    $que =$this->getQuestion($questions, $rawdata, $ass_type, $i);
                    if( $ans && $que ) {

                        $variant[] = array(
                            'question' => $questions[$rawdata->{$ass_type."_sid"}][$rawdata->{$ass_type."_qid"}]["q".$i],
                            'answer' => $ans,
                            'score' =>  $ans_value != "" ? (int)$ans_value : null ,
                            'time' => $this->CalculateTime($rawdata, $i, $type, $ass_type)
                        );
                    }
                }
                    
                else if ($i <= 16) {
                    $ans = $this->getAnswer($i, $questions, $rawdata, $ass_type, $rawdata->{$ass_type."_q" . $i});
                    $ans_value =  $rawdata->{ $ass_type."_q" . $i};
                    $que =$this->getQuestion( $questions, $rawdata, $ass_type, $i);
                    if( $ans && $que ) {
                        
                        $variant[] = array(
                            'question' => $questions[$rawdata->{$ass_type."_sid"}][$rawdata->{$ass_type."_qid"}]["q".$i],
                            'answer' => $ans,
                            'score' => $ans_value != "" ? (int)$ans_value : null,
                            'time' =>  $this->CalculateTime($rawdata, $i, $type, $ass_type)
                        );
                    }
                }
            }
       
        return $variant;
    }

    public function questionAnswerAggregate(object $rawdata, array $questions, array $answers, string $type): Array
    {
        $variant = [];
        for ($i = 1; $i <= 16; $i++) {
                if ($i < 10) {
                    $ans = getAnswer($i, (int)$rawdata->{"q0" . $i}, $answers);
                    if( $ans  ) {
                        $variant[] = array(
                            'question' => getQuestion($i, $questions),
                            'answer' => getAnswer($i, (int)$rawdata->{"q0" . $i}, $answers),
                            'score' =>  $rawdata->{"q0" . $i} != "" ? (int)$rawdata->{"q0" . $i} : null ,
                            'time' =>  CalculateTime($rawdata, $i, $type)
                        );
                    }
                }
                    
                else if ($i <= 16) {
                    $ans = getAnswer($i, (int)$rawdata->{"q" . $i}, $answers);
                    if( $ans  ) {
                        $variant[] = array(
                            'question' => getQuestion($i, $questions),
                            'answer' => getAnswer($i, (int)$rawdata->{"q" . $i}, $answers),
                            'score' => $rawdata->{"q" . $i} != "" ? (int)$rawdata->{"q" . $i} : null,
                            'time' =>  CalculateTime($rawdata, $i, $type)
                        );
                    }
                }
            }
            

        return $variant;
    }

    public function otherparam(object $record, string $type): Array
    {
        return array(
            'type' => $type,
            'id' => $record->ass_main_id,
            'is_manipulated' => $record->is_manipulated == 1 ? true : false,
            'time_until_deletion' =>  $record->round != request()->assessment_round ? null : $record->started_date,
            'time_taken' =>  $record->round != request()->assessment_round ? null : $this->TimeTaken($record->started_date, $record->completed_date, $type),
            'assessment_date' =>  $record->round != request()->assessment_round ? null : $record->started_date,
            'speed' =>  $record->assessment_round != request()->round ? null : SpeedInformation($record->speed),
        );
    }

    public function responses(array $student_data, array $assessmentTime, array $structure, object $rawdata, ?string $keyword): Array
    {
        return array(
            'id' => $assessmentTime['id'],
            'student' => array(
                'id' => $student_data['id'],
                'mis_id' => $student_data['mis_id'],
                'name' => $student_data['name'],
                'gender' => $student_data['gender'],
                'username' => $student_data['username'],
                'password' => $student_data['password'],
                'name_code' =>  $student_data['name_code'],
                'tutor' => $student_data['tutor'],
                'tutor_group' => $student_data['tutor_group'],
                'year_group' => $student_data['year_group'],
            ),
            'is_manipulated' => $rawdata->round != request()->assessment_round ? null : $assessmentTime['is_manipulated'],
            'completion_status' =>  $rawdata->round != request()->assessment_round ? 'NOT_STARTED' : GetAssessmentType($rawdata, $keyword, $assessmentTime['type']), //$assessmentTime['type'], 
            'time_until_deletion' => $assessmentTime['time_until_deletion'],
            'time_taken' =>  $rawdata->round != request()->assessment_round ? null : $assessmentTime['time_taken']['without_hour'] ?? null,
            'assessment_date' => $rawdata->round != request()->assessment_round ? null : $assessmentTime['assessment_date'],
            'tracking_speed' => $this->getSpeed ( $assessmentTime['time_taken']['with_hour'] ?? null, $rawdata ), //CompletionSpeed($student_data, $assessmentTime['speed']),
            'OUT_OF_SCHOOL' => ( isset($structure['OUT_OF_SCHOOL']) && $rawdata->round == request()->assessment_round ) ? $structure['OUT_OF_SCHOOL'] : null,
            'IN_SCHOOL' => ( isset($structure['IN_SCHOOL']) && $rawdata->round == request()->assessment_round ) ? $structure['IN_SCHOOL'] : null,

        );
    }

    public function getSpeed ( $time_taken, $rawdata ) {
        if( $time_taken == null ) 
            return null;

        if( $rawdata->round != request()->assessment_round ) {
            return null;
        }
        if( $rawdata->completed_date == '0000-00-00 00:00:00') {
            return null;
        }
        $packages = Packages( request()->school_id );
        if( in_array('safeguarding', $packages ) ) {
            if( strtotime($time_taken) <= strtotime('00:05:50') )
                return [ 'text' => 'Completed Quickly', 'label' => 'Completed Quickly', 'type' => 'QUICKLY' ];
            else if ( strtotime($time_taken) >= strtotime('00:15:40') )
                return [ 'text' => 'Completed Slowly', 'label' => 'Completed Slowly', 'type' => 'SLOWLY' ];
        }
        else {
            if( strtotime($time_taken) <= strtotime('00:03:00') )
                return [ 'text' => 'Completed Quickly', 'label' => 'Completed Quickly', 'type' => 'QUICKLY' ];
            else if ( strtotime($time_taken) >= strtotime('00:08:20') )
                return [ 'text' => 'Completed Slowly', 'label' => 'Completed Slowly', 'type' => 'SLOWLY' ];
        }

        return null;
    }

    public function getAnswer($answer_id, $questions, $rawdata, $score) {
        
        if( $rawdata->sid == null ) 
            return null;
        if( $rawdata->qid == null ) 
            return null;

        if( $rawdata->qid == -1 ) 
            return null;

        if( $rawdata->sid == -1 ) 
            return null;
           
        if( $questions == null ) 
            return null;
        
        if( $score == null || $score == 0 ) 
            return null;  

        if( !isset($questions[$rawdata->sid][$rawdata->qid]['buttons']) )
            return null;
        $options = explode('|',$questions[$rawdata->sid][$rawdata->qid]['buttons']);
        $current_option = $options[ $answer_id - 1];
        $current_option_within_question = explode('~', $current_option);
        return $current_option_within_question[$score - 1] ?? null;
        
    }

    public function getQuestion( $questions, $rawdata, $ass_type, $i) {
        if( isset( $questions[$rawdata->sid][$rawdata->qid]["q".$i] ) )
            return $questions[$rawdata->sid][$rawdata->qid]["q".$i];
        return null;
        
    }

    function CalculateTime($data, $num, $type)
    {
        if ( !isset($data->tracking->qtrack))
            return null;
        $qtrack = explode('#', $data->tracking->qtrack );
        $start_time = $end_time = null;
        $start_time = $data->tracking->start;
        if ($num == 1) {
            $end_time = isset($qtrack[0]) ? $qtrack[0] : null;
        } else if ($num < 16) {
            $end_time = isset($qtrack[$num - 1]) ? $qtrack[$num - 1] : null;
        } else if ($num == 16) {
            $end_time = isset($data->tracking->end ) ? $data->tracking->end : null;
        }
        return $this->CustomTimeTaken($start_time, $end_time, $type);
    }

    function CustomTimeTaken($started_date, $completed_date, $type)
    {
        if ($type == 'NOT_STARTED')
            return null;
        if( $type == 'INCOMPLETE')
            return '-';
        if( $completed_date == '0000-00-00 00:00:00' )
            return null;

        $completed_date = strtotime($completed_date);
        $started_date = strtotime($started_date);
        $diff = $completed_date - $started_date;
        $start_time = date('H:i:s', $started_date );
        $diff_time = gmdate('i:s', $diff );
        return $diff_time;
    }

    function TimeTaken($started_date, $completed_date, $type)
    {
        if ($type == 'NOT_STARTED' || $type == 'INCOMPLETE')
            return null;

        if( $completed_date == '0000-00-00 00:00:00' )
            return null;

        $completed_date = strtotime($completed_date);
        $started_date = strtotime($started_date);
        $diff = $completed_date - $started_date;
        return [
            'with_hour'=> gmdate("H:i:s", $diff ),
            'without_hour' => gmdate("i:s", $diff )
        ];
    }
}
