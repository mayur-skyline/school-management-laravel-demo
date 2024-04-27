<?php

namespace App\Util\Grouping\AssessmentImprovement;

use App\Models\Dbschools\Model_ass_main;

abstract class AssessmentImprovementBase
{
    protected $assMainData;

    public function __construct()
    {
        $this->assMainData = new Model_ass_main();
    }

    public function Counter($assessment, $past_assessment, $index, $counter = 0) 
    {
        if( $assessment['SELF_DISCLOSURE'] <= 7.5 )
            if( $past_assessment[$index]->P <= 7.5) 
                if( $assessment['SELF_DISCLOSURE'] > $past_assessment[$index]->P )
                    $counter++;
        else if( $assessment['SELF_DISCLOSURE'] > 7.5 )
            if( $assessment['SELF_DISCLOSURE'] < $past_assessment[$index]->P )
                    $counter++;

        if( $assessment['TRUST_OF_SELF'] <= 7.5 )
            if( $past_assessment[$index]->S <= 7.5) 
                if( $assessment['TRUST_OF_SELF'] > $past_assessment[$index]->S)
                    $counter++;
        else if( $assessment['TRUST_OF_SELF'] >= 7.5 )
            if( $assessment['TRUST_OF_SELF'] < $past_assessment[$index]->S )
                    $counter++; 

        if( $assessment['SEEKING_CHANGE'] <= 7.5 )
            if( $past_assessment[$index]->X <= 7.5) 
                if( $assessment['SEEKING_CHANGE'] > $past_assessment[$index]->X )
                    $counter++;
        else if( $assessment['SEEKING_CHANGE'] >= 7.5 )
            if( $assessment['SEEKING_CHANGE'] < $past_assessment[$index]->X )
                    $counter++; 

        if( $assessment['TRUST_OF_OTHERS'] <= 7.5 )
            if( $past_assessment[$index]->L <= 7.5) 
                if( $assessment['TRUST_OF_OTHERS'] > $past_assessment[$index]->L )
                    $counter++;
        else if( $assessment['TRUST_OF_OTHERS'] >= 7.5 )
            if( $assessment['TRUST_OF_OTHERS'] < $past_assessment[$index]->L )
                    $counter++;     
                    
        return $counter;            
    }
    abstract public function AssessmentImprovement(array $filter, string $type, int $school_id) : int;

    abstract public function StudentAssessmentImprovement( ?float $previous_score, ?float $current_score ) : bool;
}