<?php

namespace App\Util\Grouping\AssessmentImprovement;


class AssessmentImprovement extends AssessmentImprovementBase
{
    public function AssessmentImprovement(array $filter, string $type, int $school_id) : int
    {
        $type = AssessmentCat($type);
        $counter = 0; 
        $current_assessment = $this->assMainData->getAssessmentReport($filter, $type);  
        $filter = buildFetchParamForPastAssessment($filter['round'][0], $filter['academic_year'][0], $school_id);
        $past_assessment = $this->assMainData->getAssessmentReport($filter, $type); 
        foreach( $current_assessment as $assessment) 
        {
            $arr = array_column($past_assessment->toArray(), 'student_id' );
            $index = array_keys($arr, $assessment->student_id);
            if($index != -1 )
                $counter += $this->Counter( $assessment, $past_assessment, $index );
        }
        
        return $counter;
    }

    public function StudentAssessmentImprovement( ?float $current_score, ?float $previous_score ) : bool
    {
        if( $current_score === null || $previous_score === null )
            return false;

        $a = $previous_score - 7.5;// 0 - 7.5 = -7.5
        $b = $current_score - 7.5; //6 - 7.5 = -1.5
        if( abs($b) < abs($a) )
            return true;
        return false;
    }
}