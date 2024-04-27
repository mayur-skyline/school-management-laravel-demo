<?php

namespace App\Http\Controllers\AstNext;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AstNextActionPlanReportServiceProvider;
use App\Util\Builder\Report\Report;
use App\Services\AstNextServiceProvider;
use App\Services\ActionPlanServiceProvider;
use App\Services\CohortDataFilterServiceProvider;
use App\Models\Dbschools\Model_arr_year;
use App\Models\Dbschools\Model_ass_main;

class AstNextActionPlanReportController extends Controller
{
    public function __construct()
    {
        $this->report = new AstNextActionPlanReportServiceProvider();
        $this->actionPlanServiceProvider = new ActionPlanServiceProvider();
        $this->reportBuilder = new Report();
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->arrYear_model = new Model_arr_year();
        $this->assMain = new Model_ass_main();
    }

    public function student_action_plan_summary($request)
    {
        return $this->report->student_action_plan_summary( $request );
    }

    public function family_signpost_plan_summary($request)
    {
        return $this->report->family_signpost_plan_summary($request);
    }

    public function cohort_action_plan_summary(Request $request)
    {
        $finaldata1 = $this->report->cohort_action_plan_summary($request);
        return $finaldata1;
    }

    public function group_action_plan_summary(Request $request)
    {
        $finaldata1 = $this->report->group_action_plan_summary($request);
        return  $finaldata1;
    }

    public function monitor_comment_summary($request)
    {
        $this->report->monitor_comment_summary($request);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/action-plan-report",
     *      operationId="ActionPlanReport",
     *      security={{"bearer_token":{} }},
     *      tags={"Action Plan Report"},
     *      summary="Get Action Plan Report Information",
     *      description="Get Action Plan Report Information",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Parameter(
     *         ref="#/components/parameters/academicYear",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/assessmentRound",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/house",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/yearGroup",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/gender",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/campus",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/nationality",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/ethnicity",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/priorityStudents",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/compositeRisks",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/send",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/ehp",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/eal",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/lookedAfterStudents",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/GiftedAndTalented",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/pupilPremium",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/SchoolActions",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/401Responses",example="401Responses")
     *             },
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *   )
     *
     */

    public function report(Request $request)
    {
        $date = date("Y/m/d");
        $school_id = $request->get('school_id');
        $query_filter = $this->CohortDataFilterServiceProvider->QueryFilters($request);
        $year = IsDataAvailableInYear($school_id);
        $round = RoundLatest($year);
        !isset($query_filter['academic_year'])?$query_filter['academic_year'] = $year:$query_filter['academic_year'];
        !isset($query_filter['assessment_round'])?$query_filter['assessment_round'] = $round:$query_filter['assessment_round'];

        $meta_reponses = $this->response_meta($request);
        $family_signpost_plan_summary = $this->family_signpost_plan_summary( $request );
        $student_action_plan_summary = $this->student_action_plan_summary( $request );
        $cohort_action_plan_summary = $this->cohort_action_plan_summary($request);
        $group_action_plan_summary = $this->group_action_plan_summary($request);
        $monitor_comment_summary = null;//$this->monitor_comment_summary();
        return  $this->reportBuilder->buildActionPlanReport($meta_reponses['generated'], $meta_reponses['no_of_students'],$student_action_plan_summary, $family_signpost_plan_summary,
                                                            $cohort_action_plan_summary, $group_action_plan_summary,
                                                            $monitor_comment_summary
                                                         );
    }

    public function meta($request): array
    {
        $school_id = $request->get('school_id');
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = setDefaultFilter($filter, $school_id);
        return ['filter' => $filter, 'school_id' => $school_id];
    }

    public function meta2($request): array
    {
        $school_id = $request->get('school_id');
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = setDefaultFilter($filter, $school_id);
        return ['filter' => $filter, 'school_id' => $school_id];
    }

    public function response_meta($request): array
    {
        list('filter' => $filter) = $this->meta($request);
        $out_school_data =  $this->assMain->getAssessmentReport($filter, ['at']);
        $in_school_data =  $this->assMain->getAssessmentReport($filter, ['hs', 'sch' ]);
        $school_data = returnHighestValueData( $in_school_data, $out_school_data );
        return ['no_of_students' => count($school_data), 'generated' => date('Y-m-d')];
    }
}
