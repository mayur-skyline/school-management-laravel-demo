<?php
namespace App\Services\ESR;
use App\Models\Dbschools\Model_multischools;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbglobal\Model_groupdash;
use App\Models\Dbglobal\Model_groupRisk;
use App\Util\Grouping\RoundManagement\Round;
use App\Util\Grouping\CommonRisk\Risk;
use App\Services\CohortDataFilterServiceProvider;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_report_actionplan;
use App\Models\Dbschools\Model_report_family_signpost;
use App\Models\Dbschools\Model_rep_group_actionplan;
use App\Models\Dbschools\Model_rep_group_pdf;
use App\Models\Dbschools\Model_arr_year;
use App\Services\ActionPlanServiceProvider;
use DB;

class AstNextKeyImplementationMetricsServiceProvider
{
    public function __construct()
    {
        $this->multiSchool = new Model_multischools;
        $this->groupdash = new Model_groupdash();
        $this->datSchool = new Model_dat_schools();
        $this->groupRisk = new Model_groupRisk();
        $this->round = new Round();
        $this->commonRisk = new Risk();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->assMain = new Model_ass_main();
        $this->studentAP = new Model_report_actionplan();
        $this->studentFS = new Model_report_family_signpost();
        $this->studentGAP = new Model_rep_group_actionplan();
        $this->studentCAP = new Model_rep_group_pdf();
        $this->arrYear = new Model_arr_year();
        $this->actionPlanServiceProvider = new ActionPlanServiceProvider();
    }

    public function filter($year, $round) {
        $filter['campus'] = request()->get('campus') ?? request()->input('campus');
        $filter['academic_year'][] = $year;
        $filter['round'][] = $round;
        return $filter;
    }

    public function getAssessmentWithAP($actionPlans, $pupil_id, $pupilList) {
        $apList = [];
        foreach ($actionPlans as $key => $ap) {
            if( in_array( $ap->{$pupil_id},  $pupilList) )
                $apList[] = collect($ap);
        }
        return collect($apList);
    }

    public function fetchAllAssessmentinYear($filter) {
        $counter = 1;
        $in_assessments = $out_assessments = null;
        $firstCompletedAssessment = $this->assMain->GetFirstCompletedAssessment($filter);
        $assessmentList = $this->assMain->GetAllAssessmentOnAndAfterDate($firstCompletedAssessment, $filter['academic_year'][0]);
        $pupil_ids = $assessmentList->pluck('pupil_id')->toArray();
        //return $assessmentList;
        //Get all SP
        $studentAP = $this->studentAP->PupilWithActionPlan($filter, null, $firstCompletedAssessment, null );
        $studentAP = $this->getAssessmentWithAP($studentAP, 'created_on', $pupil_ids);
        //Get all FS
        $studentFS = $this->studentFS->PupilWithActionPlan($filter, null, $firstCompletedAssessment, null );
        $studentFS = $this->getAssessmentWithAP($studentFS, 'created_on', $pupil_ids);
        //Get all GAP
        $studentGAP = $this->studentGAP->PupilWithActionPlan($filter, null, $firstCompletedAssessment, null );
        //Get all CAP
        $studentCAP = $this->studentCAP->PupilWithActionPlan($filter, $firstCompletedAssessment );


        return [ 
            'studentAP' => $studentAP, 
            'studentFS' => $studentFS,
            'studentGAP' => $studentGAP, 
            'studentCAP' => $studentCAP
         ];
    }

    public function fetchStudentByGroup($year,$group) {
        $result = $this->arrYear->getSpecificGroup($year, $group);
        $data = $result->groupBy('value')->all();
        $group = array_keys($data);
        $ids = [];
        foreach ($group as $key => $grp) {
            $ids[$grp] = $data[$grp]->pluck('name_id');
        }
        return [ 'groupdata' => $data, 'ids' => $ids ];
    }

    public function getStudentWithInGroup($actionPlans, $group) {
        $users_with_grp_ap = [];
        foreach ($actionPlans as $key => $ap) {
            $pop_ids = explode(',', $ap->pop_id);
            $common = array_intersect($pop_ids, $group->toArray());
            if( count($common) > 0) $users_with_grp_ap[] = $ap;
        }
        return $users_with_grp_ap;
    }

    public function ExtractStudentIDFromGroup( $group, $student_ids, $type ) {
        $list = [];
        foreach($group as $ap) {
            $pop_ids = explode(',',$ap['student']['id']);
            $intersect = array_intersect($pop_ids,$student_ids);
            if( count($intersect) > 0 )  {
                $list[] = $ap;
            }
        }
        
        return count($list);
    }

    public function actionPlanByGroup( $groupdata, $ids, $sapdata, $fsdata, $capdata, $gapdata) {

        $groupIds = array_keys($ids);
        $sapIds = collect($sapdata['sap'])->pluck('created_on')->toArray();
        $fsIds = collect($fsdata['fs'])->pluck('created_on')->toArray();
        $groupSAPCount = $groupFSCount =  $groupGAPCount = $groupCAPCount = null;
        foreach ($groupIds as $key => $grpId) 
          $groupSAPCount[ $grpId ] = count( array_values( array_intersect($sapIds, $ids[$grpId]->toArray()) ) ) ?? 0;
        foreach ($groupIds as $key => $grpId) 
          $groupFSCount[ $grpId ] = count( array_values( array_intersect($fsIds, $ids[$grpId]->toArray()) ) ) ?? 0;
        foreach ($groupIds as $key => $grpId) 
          $groupGAPCount[ $grpId ] = $this->ExtractStudentIDFromGroup( $gapdata['gap'] ?? [], $ids[$grpId]->toArray(), 'gap' );
        foreach ($groupIds as $key => $grpId) 
          $groupCAPCount[ $grpId ] = $this->ExtractStudentIDFromGroup( $capdata['cap'] ?? [], $ids[$grpId]->toArray(), 'cap' );
        return [ 
            'groupSAPCount' => $groupSAPCount, 
            'groupFSCount' => $groupFSCount, 
            'groupGAPCount' => $groupGAPCount, 
            'groupCAPCount' => $groupCAPCount 
        ];
    }

    public function aggregate($sapdata, $fsdata, $capdata, $gapdata) {
        $total = ( $sapdata['total'] ?? 0 ) + ( $fsdata['total'] ?? 0 ) + ( $capdata['total'] ?? 0 ) + ( $gapdata['total'] ?? 0 );
        return [
            "total_action_plan" => $total,
            "student_action_plan"=> $sapdata['total'] ?? 0,
            "family_signpost"=>  $fsdata['total'] ?? 0,
            "group_action_plan"=> $gapdata['total'] ?? 0,
            "cohort_action_plan"=> $capdata['total'] ?? 0
        ];
    }

    public function groupSummary( $group, $groupAP, $type ) {
        $groupList = array_keys($group);
        sort($groupList);
        natsort($groupList);
        $actionPlanByGroup = [];
        foreach ($groupList as $key => $value) {
            $actionPlanByGroup[] = [
                "name" => "$type $value",
                "action_plans" => [
                    "student_action_plan" =>  $groupAP['groupSAPCount'][$value],
                    "family_signpost" => $groupAP['groupFSCount'][$value],
                    "group_action_plan" => $groupAP['groupGAPCount'][$value],
                    "cohort_action_plan" => $groupAP['groupCAPCount'][$value],
                ]
                ];
        }
        return $actionPlanByGroup;
    }

    public function historicalstudentActionPlans( $filter, $year )
    {
        $yearList[] = $year;
        $total = 0;
        $list = [];
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalstudentActionPlans($filter, $yearList);
        $data = CheckAndRemoveFalseDataByRoundHistoric( $data, $filter );
        // $total = count( $data[$year.'-'.$round] ?? [] );
        // $list = $data[$year.'-'.$round] ?? [];
        $groups = array_keys($data);
        foreach ($groups as $key => $grp) {
            $total = $total + count($data[$grp]);
            $list = array_merge( $list, $data[$grp]);
        }
        return [ 'sap' => $list, 'total' => $total ];
    }

    public function historicalstudentFamilySignPostActionPlans( $filter, $year )
    {
        $yearList[] = $year;
        $total = 0;
        $list = [];
        $filter = historyFilterByRound( $filter, [ 'at' ] );
        $data = $this->actionPlanServiceProvider->historicalstudentFamilySignPostActionPlans($filter, $yearList);
        $data = CheckAndRemoveFalseDataByRoundHistoric( $data, $filter );
        // $total = count( $data[$year.'-'.$round] ?? [] );
        // $list = $data[$year.'-'.$round] ?? [];
        $groups = array_keys($data);
        foreach ($groups as $key => $grp) {
            $total = $total + count($data[$grp]);
            $list = array_merge( $list, $data[$grp]);
        }
        return [ 'fs' => $list, 'total' => $total ];
    }

    public function historiccohortactionplan( $filter, $year )
    {
        $yearList[] = $year;
        $total = 0;
        $list = [];
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalCohortstudentActionPlans($filter, $yearList);
        $processedData = $this->actionPlanServiceProvider->ProcessCohortData($data, 'COHORT_ACTION_PLAN');
        // $total = count( $processedData->{$year}[$round] ?? [] );
        // $list = $processedData->{$year}[$round] ?? [];
        $groups = array_keys($processedData->{$year} ?? []);
        foreach ($groups as $key => $grp) {
            $total = $total + count($processedData->{$year}[$grp]);
            $list = array_merge( $list, $processedData->{$year}[$grp]);
        }
        return [ 'data' => $data, 'cap' => $list, 'total' => $total ];
    }

    public function historicgroupactionplan( $filter, $year )
    {
        $yearList[] = $year;
        $total = 0;
        $list = [];
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalGroupstudentActionPlans($filter, $yearList);
        $processedData = $this->actionPlanServiceProvider->ProcessGroupData($data, 'GROUP_ACTION_PLAN');
        // $total = count( $processedData->{$year}[$round] ?? [] );
        // $list = $processedData->{$year}[$round] ?? [];
        $groups = array_keys($processedData->{$year} ?? []);
        foreach ($groups as $key => $grp) {
            $total = $total + count($processedData->{$year}[$grp]);
            $list = array_merge( $list, $processedData->{$year}[$grp]);
        }
        return [ 'gap' => $list, 'total' => $total ];
    }

    public function keyImplementationMetrics($request, $school_id) {
        $year = $request->get('academic_year') ?? $this->datSchool->SchoolAcademicYear( $school_id );
        $filter = $this->filter( $year, $request->get('assessment_round') );
        if( count( $filter['campus'] ?? [] ) == 1 ) 
            $round = $request->get('assessment_round') ?? $this->round->getInProgressRoundForSpecCampus( $school_id, $year, $filter['campus'][0] );
        else {
            $campus_year_round = $this->round->getAllCampusInProgressRound( $school_id, $year, [] );
            $round = $request->get('assessment_round') ?? collect($campus_year_round)->max('round') ?? 1;
        }
        $sapdata = $this->historicalstudentActionPlans( $filter, $year );
        $fsdata =  $this->historicalstudentFamilySignPostActionPlans( $filter, $year );
        $capdata = $this->historiccohortactionplan( $filter, $year );
        $gapdata = $this->historicgroupactionplan( $filter, $year );
        $aggregate = $this->aggregate($sapdata, $fsdata, $capdata, $gapdata);
        [ 'ids' => $ids, 'groupdata' => $groupdata ] = $this->fetchStudentByGroup($year,'year');
        $year_groups = $this->actionPlanByGroup( $groupdata, $ids, $sapdata, $fsdata, $capdata, $gapdata);
        $actionPlanByYearGroup = $this->groupSummary($groupdata, $year_groups, "Year");

        [ 'ids' => $ids, 'groupdata' => $groupdata ] = $this->fetchStudentByGroup($year,'house');
        $house_groups = $this->actionPlanByGroup( $groupdata, $ids, $sapdata, $fsdata, $capdata, $gapdata );
        $actionPlanByHouseGroup = $this->groupSummary( $groupdata, $house_groups, "");
        return [
            "aggregate" => $aggregate,
            "comparism" => [
                "year" => $actionPlanByYearGroup,
                "house" => $actionPlanByHouseGroup
            ]
        ];
    }



}