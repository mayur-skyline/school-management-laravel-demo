<?php

namespace App\Util\Grouping\ActionPlan;

use App\Services\ActionPlanMetaServiceProvider;
use App\Util\Grouping\ActionPlan\ActionPlanBase;
use Illuminate\Support\Facades\Log;

class ActionPlan extends ActionPlanBase
{
    protected $allactionPlans;
    public function studentActionPlans(string $student_id, string $school_id, $request): Array
    {
        list('pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList) = $this->Parameters($student_id, $school_id, $request);
        $data = $this->actionPlanService->getCompletedAssessmentInformation($filter, $yearList, $student_id );
        $actionPlans =  $this->actionPlan->getActionPlansForStudent($student_id, $filter, $data);
        return $this->actionBuilder->buildPupilActionPlan($actionPlans, 'STUDENT_ACTION_PLAN', $pupil);
    }

    public function familySignPost(string $student_id, string $school_id, $request): Array
    {
        list('pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList) = $this->Parameters($student_id, $school_id, $request );
        $data = $this->actionPlanService->getCompletedAssessmentInformation($filter, $yearList, $student_id );
        $actionPlans =  $this->familySignPost->getActionPlansForStudent($student_id, $filter, $data);
        $plans = $this->actionBuilder->buildPupilActionPlan($actionPlans, 'FAMILY_SIGNPOST', $pupil);
        //$monitorplans = $this->monitorComment( $student_id, $school_id, $request );
        return $plans;
    }

    public function monitorComment(string $student_id, string $school_id, $request ): Array
    {
        list('pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList) = $this->Parameters($student_id, $school_id, $request);
        $data = $this->actionPlanService->getCompletedAssessmentInformation($filter, $yearList, $student_id );
        $actionPlans =  $this->monitor->getActionPlansForStudent($student_id, $filter, $data);
        return $this->actionBuilder->buildPupilMonitorComment($actionPlans, 'MONITOR_COMMENT', $pupil);
    }

    public function groupActionPlans( string $student_id, string $school_id, $request ): Array
    {
        $studentPlans = [];
        list('pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList) = $this->Parameters($student_id, $school_id, $request );
        $data = $this->actionPlanService->getCompletedAssessmentInformation($filter, $yearList, $student_id );
        $actionPlans =  $this->groupactionPlan->getActionPlansForStudent($student_id, $filter, $data);
        foreach( $actionPlans as $plan ) {
            $student_list = explode(',', $plan->pop_id );
            if( in_array( $student_id, $student_list ) ) {
                $studentPlans[] =  $this->actionBuilder->CollectionActionPlanResponses( $plan, 'GROUP_ACTION_PLAN', 'Group Action Plan', null, null );
            }
                
        }
        return $studentPlans;
    }

    public function cohortActionPlans( string $student_id, string $school_id, $request ): Array
    {
        $studentPlans = [];
        list('pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList) = $this->Parameters($student_id, $school_id, $request );
        $data = $this->actionPlanService->getCompletedAssessmentInformation($filter, $yearList, $student_id );
        $actionPlans =  $this->cohortactionPlan->getActionPlansForStudent($student_id, $filter, $data);
        foreach( $actionPlans as $plan ) {
            $student_list = explode(',', $plan->pop_id );
            if( in_array( $student_id, $student_list ) ) {
                $studentPlans[] =  $this->actionBuilder->CollectionActionPlanResponses( $plan, 'COHORT_ACTION_PLAN', 'Cohort Action Plan', null, null);
            }
                
        }
        return $studentPlans;
    }

    public function fetchallCurrentActionPlans($student_id, $school_id, $request): Array
    {
        $splans = $this->studentActionPlans($student_id, $school_id, $request);
        $gplans = $this->groupActionPlans( $student_id, $school_id, $request );
        $cplans = $this->cohortActionPlans( $student_id, $school_id, $request );
        $mplans = $this->monitorComment( $student_id, $school_id, $request );
        return array_merge($splans, $gplans, $cplans, $mplans);
    }

    public function merge($studentplans, $groupplans, $cohortplans): Array
    {
        return array_merge($studentplans, $groupplans);
    }

    public function Parameters($student_id, $school_id, $request)
    {
        $actionPlanMeta = new ActionPlanMetaServiceProvider();
        $pupil = $this->population->get($student_id);
        $filter = $this->filter($school_id, $request );
        $yearList = $actionPlanMeta->academicYearsList( $school_id );

        return ['pupil' => $pupil, 'filter' => $filter, 'yearList' => $yearList];
    }
}
