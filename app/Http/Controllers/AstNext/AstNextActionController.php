<?php

namespace App\Http\Controllers\AstNext;

use App\Http\Controllers\Controller;
use App\Http\Requests\FamilySignPost;
use Illuminate\Http\Request;
use App\Services\AstNextServiceProvider;
use App\Services\AstNextAssessmentDataServiceProvider;
use App\Services\CohortDataFilterServiceProvider;
use App\Services\PopulationServiceProvider;
use App\Services\RedisServiceProvider;
use App\Http\Requests\StudentActionPlan;
use App\Services\ActionPlanServiceProvider;
use App\Services\AstNextActionPlanStatementServiceProvider;
use App\Services\ActionPlanMetaServiceProvider;
use App\Models\Dbglobal\Model_str_bank_statements;
use App\Services\CohortServiceProvider;
use App\Models\Dbschools\Model_rep_group_pdf;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_rep_group_actionplan;
use App\Models\Dbglobal\Model_str_groupbank_statements;
use App\Models\Dbglobal\Model_str_groupbank_sections;
use App\Models\Dbglobal\Model_str_groupbank_questions;
use App\Models\Dbschools\Model_school_table_exist;
use App\Models\Dbglobal\Model_str_bank_questions_ui_ux;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_ass_main;
use DB;
use Carbon\Carbon;

class AstNextActionController extends Controller
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->astNextServiceAssessmentDataServiceProvider = new AstNextAssessmentDataServiceProvider();
        $this->populationServiceProvider = new PopulationServiceProvider();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->redisServiceProvider = new RedisServiceProvider();
        $this->actionPlanServiceProvider = new ActionPlanServiceProvider();
        $this->actionStatement = new AstNextActionPlanStatementServiceProvider();
        $this->actionPlanMeta = new ActionPlanMetaServiceProvider();
        $this->statement = new Model_str_bank_statements();
        $this->cohortServiceProvider = new cohortServiceProvider();
        $this->rep_group_pdf = new Model_rep_group_pdf();
        $this->population_model = new Model_population();
        $this->rep_group_actionplan_model = new Model_rep_group_actionplan();
        $this->str_groupbank_statements_model = new Model_str_groupbank_statements();
        $this->str_groupbank_sections = new Model_str_groupbank_sections();
        $this->str_groupbank_questions = new Model_str_groupbank_questions();
        $this->schoolTableExist_model = new Model_school_table_exist();
        $this->question = new Model_str_bank_questions_ui_ux();
        $this->datSchools_model = new Model_dat_schools();
        $this->assMain = new Model_ass_main();
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/student-action-plans",
     *      operationId="studentActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Get list of Student Action Plan",
     *      description="Get list of Student Action Plan",
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
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
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

    public function studentActionPlans(Request $request)
    {
        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $meta = $this->populationServiceProvider->Metadata($request);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $data = $this->actionPlanServiceProvider->studentActionPlans($filter, $meta, $yearList);
        $data = CheckAndRemoveFalseDataByRound( $data, $filter, [ 'sch', 'hs' ] );
        $data = paginate( $request->get('size'), $request->get('page'), count( $data ), $data );
        $processedData = $this->actionPlanServiceProvider->ProcessData($data, 'STUDENT_ACTION_PLAN');
        $fetchmeta = $this->actionPlanServiceProvider->FetchManagement($data, $meta);
        return response()->json([
            "data" => $processedData,
            "meta" => $fetchmeta
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/family-signposts",
     *      operationId="familySignPost",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Get list of Family Signpost",
     *      description="Get list of Family Signpost",
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
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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


    public function studentFamilySignPostActionPlans(Request $request)
    {
        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $meta = $this->populationServiceProvider->Metadata($request);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $data = $this->actionPlanServiceProvider->studentFamilySignPostActionPlans($filter, $meta, $yearList);
        $data = CheckAndRemoveFalseDataByRound( $data, $filter, [ 'at' ] );
        $data = paginate( $request->get('size'), $request->get('page'), count( $data ), $data );
        $processedData = $this->actionPlanServiceProvider->ProcessData($data, 'FAMILY_SIGNPOST');
        $fetchmeta = $this->actionPlanServiceProvider->FetchManagement($data, $meta);
        return response()->json([
            "data" => $processedData,
            "meta" => $fetchmeta
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/student-action-plans/{id}",
     *      operationId="StudentActionDetail",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Get Detail Student Action Plan",
     *      description="Get Detail Student Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/ActionPlanDetailResponses",example="ActionPlanDetailResponses")
     *             },
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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
    public function studentActionPlanDetail(Request $request, $id)
    {
        $rawdata = $this->actionPlanServiceProvider->studentActionPlanDetail($id);
        if ($rawdata == null) {
            return response()->json([
                "message" => "Action Plan not Found"
            ], 404);
        }
        $rawdata->isNameCode = IsSchoolNameCode();
        $processedData = $this->actionPlanServiceProvider->ProcessData([$rawdata], 'STUDENT_ACTION_PLAN');
        $structuredData = $this->actionStatement->ExtractGoalSignPostAction($rawdata);
        $actions = $this->actionStatement->FetchActions($structuredData['actions']);
        $goals = $this->actionStatement->FetchExtractedData($structuredData['goalsignpost'], $rawdata, 'con');
        $causes = $this->actionStatement->ExtractAndFetchCauses($rawdata, 'con');
        $risks = $this->actionStatement->ExtractRisks($rawdata);
        $risks = $this->actionStatement->FetchRisks($risks, 'con', $rawdata);
        $scores = $this->actionStatement->FetchScoreHistory($request, $rawdata->student_id, 3);
        $factor_bias = $this->actionPlanMeta->getFactorBias($rawdata, $scores);
        $description = $this->actionPlanMeta->getDescription($rawdata, $factor_bias);
        $data = $this->actionPlanServiceProvider->combineResponseData($processedData[0], $goals, $actions, $description, $causes, $risks, $scores);
        return $data;
    }


    /**
     * @OA\Get(
     *      path="/api-astnext/family-signposts/{id}",
     *      operationId="familySignPostDetail",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Get Detail of Family Signpost",
     *      description="Get Detail of Family Signpost",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/ActionPlanDetailResponses",example="ActionPlanDetailResponses")
     *             },
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function familySignPostDetail(Request $request, $id)
    {
        $rawdata = $this->actionPlanServiceProvider->familySignPostDetail($id);
        if ($rawdata == null) {
            return response()->json([
                "message" => "Action Plan not Found"
            ], 404);
        }
        $rawdata->isNameCode = IsSchoolNameCode();
        $processedData = $this->actionPlanServiceProvider->ProcessData([$rawdata], 'FAMILY_SIGNPOST');
        if (isset($rawdata->version) && $rawdata->version == 2) {
            $structuredData = $this->actionStatement->ExtractGoalSignPostAction($rawdata);
            $actions = $this->actionStatement->FetchActions($structuredData['actions']);
            $goals = $this->actionStatement->FetchExtractedData($structuredData['goalsignpost'], $rawdata, 'gen');
            //$causes = $this->actionStatement->ExtractAndFetchCauses($rawdata, 'gen');
            //$risks = $this->actionStatement->ExtractRisks($rawdata);
            //$risks = $this->actionStatement->FetchRisks($risks, 'gen', $rawdata);
        } else {
            $goals = $this->actionStatement->ExtractAndFetchGoalsFamilySignpost($rawdata, 'gen');
            //$risks = $this->actionStatement->ExtractRisks($rawdata, 'gen');
            //$risks = $this->actionStatement->FetchRisks($risks, 'gen', $rawdata);
            //$causes = [];
            $actions = "";
        }

        $scores = $this->actionStatement->FetchScoreHistory($request, $rawdata->student_id, 1);
        $factor_bias = $this->actionPlanMeta->getFactorBias($rawdata, $scores);
        $description = $this->actionPlanMeta->getDescription($rawdata, $factor_bias);
        $data = $this->actionPlanServiceProvider->combineResponseDataFamilySignPost($processedData[0], $goals, $actions, $description, $scores);
        return $data;
    }

    /**
     * @OA\Post(
     *      path="/api-astnext/create-student-action-plan",
     *      operationId="CreateStudentActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Create Student Action Plan",
     *      description="Create Student Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              required={"student_id","future_risk_ids", "reason_ids", "goal_id", "signpost_id", "school_action", "lead", "review_date"},
     *              @OA\Property(
     *                  property="student_id",
     *                  type="integer"
     *               ),
     *              @OA\Property(property="risk", type="string", example="18"),
     *              @OA\Property(
     *                  property="reason_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="future_risk_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(property="goal_id", type="integer", example="1"),
     *              @OA\Property(property="signpost_id", type="integer", example="1"),
     *              @OA\Property(property="school_action", type="string", example="Albert is XYZ"),
     *              @OA\Property(property="lead", type="string", description="Who is Responsible"),
     *              @OA\Property(property="review_date", type="date"),
     *           ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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


    public function createstudentactionplan(StudentActionPlan $request)
    {
        $bias = GetBiasInAbbrev($request->risk);
        $section1 = $this->actionPlanMeta->BuildSection1($request->reason_ids);
        $section2 = $this->actionPlanMeta->BuildSection2($request->future_risk_ids);
        $section3 = $this->actionPlanMeta->BuildSection3($request);
        $combine = $this->actionPlanMeta->CombineSections($section1, $section2, $section3);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $pupil_last_assess_info = $this->actionPlanMeta->IsDataAvailableInYearParticularPupil($yearList, $request->student_id);
        $postdata = $this->actionPlanMeta->Commit($request, $combine, $bias, $pupil_last_assess_info);
        return response()->json($postdata, 200);
    }

    /**
     * @OA\Post(
     *      path="/api-astnext/create-family-signpost",
     *      operationId="CreateFamilySignPost",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Create Family SignPost",
     *      description="Create Family SignPost",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              required={"student_id","future_risk_ids", "reason_ids", "goals", "school_action", "lead", "review_date"},
     *              @OA\Property(
     *                  property="student_id",
     *                  type="integer"
     *               ),
     *              @OA\Property(property="risk", type="string", example="18"),
     *              @OA\Property(
     *                  property="reason_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="future_risk_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="goals",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer"),
     *                      @OA\Property(property="goal", type="string"),
     *                      @OA\Property(
     *                          property="signposts",
     *                          type="array",
     *                          @OA\Items(
     *                               @OA\Property(property="id", type="integer"),
     *                               @OA\Property(property="signpost", type="string"),
     *                           ),
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(property="school_action", type="string", example="Albert is XYZ"),
     *              @OA\Property(property="lead", type="string", description="Who is Responsible"),
     *              @OA\Property(property="review_date", type="date"),
     *           ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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
    public function createfamilysignpost(FamilySignPost $request)
    {
        $bias = GetBiasInAbbrev($request->risk);
        $section1 = array('section_1' => []); //$this->actionPlanMeta->BuildSection1($request->reason_ids);
        $section2 = array('section_2' => array("" => [])); //$this->actionPlanMeta->BuildSection2($request->future_risk_ids);
        $section3 = $this->actionPlanMeta->BuildSectionFamilySignpost3($request);
        $combine = $this->actionPlanMeta->CombineSections($section1, $section2, $section3);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $pupil_last_assess_info = $this->actionPlanMeta->IsDataAvailableInYearParticularPupil($yearList, $request->student_id);
        $postdata = $this->actionPlanMeta->CommitFamilySignPost($request, $combine, $bias, $pupil_last_assess_info);
        return response()->json($postdata, 200);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/student-action-plans-history",
     *      operationId="HistoricalStudentAction",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Get Historical Student Action Plan",
     *      description="Get Historical Student Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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


    public function historicalstudentActionPlans(Request $request)
    {
        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $filter = $this->actionPlanMeta->IsDataAvailableInYear($yearList, $filter);
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalstudentActionPlans($filter, $yearList);
        $data = CheckAndRemoveFalseDataByRoundHistoric( $data, $filter );
        $processedData = $this->actionPlanServiceProvider->ProcessHistoricalData($data, 'STUDENT_ACTION_PLAN');
        return response()->json([
            "data" => $processedData
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/family-signposts-history",
     *      operationId="HistoricalFamilySignPost",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Get Historical Family SignPost",
     *      description="Get Historical Family SignPost",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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



    public function historicalstudentFamilySignPostActionPlans(Request $request)
    {
        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $filter = $this->actionPlanMeta->IsDataAvailableInYear($yearList, $filter);
        $filter = historyFilterByRound( $filter, [ 'at' ] );
        $data = $this->actionPlanServiceProvider->historicalstudentFamilySignPostActionPlans($filter, $yearList);
        $data = CheckAndRemoveFalseDataByRoundHistoric( $data, $filter );
        $processedData = $this->actionPlanServiceProvider->ProcessHistoricalData($data, 'FAMILY_SIGNPOST');
        return response()->json([
            "data" => $processedData
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/students/{id}/reasons",
     *      operationId="reasons",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment Meta Data"},
     *      summary="Get List of Reasons",
     *      description="Get List of Reasons",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
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



    public function getCauses(Request $request, $id)
    {
        $finalarray = [];
        $pupil = Model_population::where('id', $id)->first();
        $school_id = $request->get('school_id');
        $selected_type = "IN_SCHOOL";
        $dbname = getSchoolDatabase($school_id);
        DB::purge('school_db');
        schoolDb($dbname, $school_id);
        $bias_name = $this->actionPlanMeta->biasName($request->get('risk'));
        $statement = $this->statement->getAbbrevStatement($bias_name);
        if ($bias_name == 'SOCIAL NAIVETY')
            $statement = 'sn';
        if ($statement == 'or' || $statement == 'hv' || $statement == 'sn' || $statement == 'sci' || $statement == 'ha' || $statement == 'blu') {
            //$selected_type = 'composite';
            $return_array = $this->actionPlanServiceProvider->getCausesRiskSignpostuiux($selected_type, $statement, 1, [], []);
            if ($pupil && !empty($return_array)) {
                $fullname = ucwords($pupil->firstname . " " . $pupil->lastname);
                $gender = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "his" : "her";
                $gender1 = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "he" : "she";
                $gender2 = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "him" : "her";
                foreach ($return_array['questions_detail'] as $key => $row) {

                    $question = str_replace("##name##", $fullname, $row['question']);
                    $question = str_replace("##his/her##", $gender, $question);
                    $question = str_replace("##s/he##", $gender1, $question);
                    $question = str_replace("##S/he##", $gender1, $question);
                    $question = str_replace("##him/her##", $gender2, $question);
                    $question = str_replace("him/her", $gender2, $question);
                    $qustions[$key] = trim($question);
                }
                $title = str_replace("##name##", $fullname, $return_array['title_section']);
                $title = str_replace("##his/her##", $gender, $title);
                $title = str_replace("##s/he##", $gender1, $title);
                $title = str_replace("##S/he##", $gender1, $title);
                $title = str_replace("##him/her##", $gender2, $title);
                $finalarray['title'] = isset($title)?trim($title):'';
                if(isset($return_array['questions_detail']) && !empty($return_array['questions_detail'])){
                    foreach ($return_array['questions_detail'] as $k => $r) {
                        $finalarray['reasons'][$k]['id'] = $r['question_id'];
                        foreach ($qustions as $k1 => $r1) {
                            if ($k == $k1)
                                $finalarray['reasons'][$k]['reason'] = $r1;
                        }
                    }
                }else{
                    $finalarray['reasons'] = [];
                }
            }
            if (isset($finalarray) && !empty($finalarray)) {
                return response()->json(
                    $finalarray
                );
            } else {
                return response()->json(
                    []
                );
            }
        }
        else{
            //$selected_type = 'polar';
            $return_array = $this->actionPlanServiceProvider->getCausesRiskSignpostuiux($selected_type, $statement, 1, [], [], true);
            if ($pupil && !empty($return_array)) {
                $fullname = ucwords($pupil->firstname . " " . $pupil->lastname);
                $gender = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "his" : "her";
                $gender1 = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "he" : "she";
                $gender2 = strtolower(substr($pupil->gender, 0, 1)) == "m" ? "him" : "her";

                foreach ($return_array['questions_detail'] as $key => $row) {

                    $question = str_replace("##name##", $fullname, $row['question']);
                    $question = str_replace("##his/her##", $gender, $question);
                    $question = str_replace("##s/he##", $gender1, $question);
                    $question = str_replace("##S/he##", $gender1, $question);
                    $question = str_replace("##him/her##", $gender2, $question);
                    $question = str_replace("him/her", $gender2, $question);
                    $qustions[$key] = trim($question);
                }
                $title = str_replace("##name##", $fullname, $return_array['title_section']);
                $title = str_replace("##his/her##", $gender, $title);
                $title = str_replace("##s/he##", $gender1, $title);
                $title = str_replace("##S/he##", $gender1, $title);
                $title = str_replace("##him/her##", $gender2, $title);
                $finalarray['title'] = isset($title)?trim($title):'';
                if(isset($return_array['questions_detail']) && !empty($return_array['questions_detail'])){
                    foreach ($return_array['questions_detail'] as $k => $r) {
                        $finalarray['reasons'][$k]['id'] = $r['question_id'];
                        foreach ($qustions as $k1 => $r1) {
                            if ($k == $k1)
                                $finalarray['reasons'][$k]['reason'] = $r1;
                        }
                    }
                }else{
                    $finalarray['reasons'] = [];
                }
            }
            if (isset($finalarray) && !empty($finalarray)) {
                return response()->json(
                    $finalarray
                );
            } else {
                return response()->json(
                    []
                );
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/cohort-action-plans",
     *      operationId="cohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Get list of Cohort Action Plan",
     *      description="Get list of Cohort Action Plan",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
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


    public function currentcohortactionplan(Request $request)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $formated_cohort_actionplan = [];
        //Code to allow and show all the action plan created irrespectie of the filters applied ---start
        $year = $request->get("academic_year");
        $filter_year = array('accyear' => $year);
        $filter_year = http_build_query($filter_year);
        $assessment_round = $request->get("assessment_round");
        $filter_assessment_round = array('assessment_round' => $assessment_round);
        $filter_assessment_round = http_build_query($filter_assessment_round);
        $finalstrings = $filter_year . '%%' . $filter_assessment_round;
        //Code to allow and show all the action plan created irrespectie of the filters applied ---end
        $datacohort = $this->rep_group_pdf->getcohortactionplandatas($finalstrings);
        if (isset($datacohort) && !$datacohort->isEmpty() && $datacohort !== 'FALSE') {
            foreach ($datacohort as $a) {
                if (isset($a['pop_id']) && !empty($a['pop_id'])) {
                    $risk = $a['type_banc'];
                    $filter = isset($a['filter']) ? $a['filter'] : '';
                    if (isset($a['filter']))
                        $final_filter = $this->actionPlanServiceProvider->Display_title_filters($filter);
                    if ($risk == 'or' || $risk == 'hv' || $risk == 'sn' || $risk == 'sci' || $risk == 'ha')
                        $riskType = "COMPOSITE_RISK";
                    else
                        $riskType = "POLAR_BIAS";
                    $str_arr = explode(",", $a['pop_id']);
                    if (!isset($final_filter))
                        $final_filter = $finalstring['title'];
                        $formated_cohort_actionplan[] = $this->actionPlanServiceProvider->FormatStudentCohortActionPlan($a, 'COHORT_ACTION_PLAN', [], $final_filter, $riskType);
                    unset($names);
                }
            }
            $tmp = $formated_cohort_actionplan;
            $page = $request->has('page') ? $request->get('page') : 1;
            $size = $request->has('size') ? $request->get('size') : 15;
            $paginated_response = array_slice($tmp, ($page - 1) * $size, $size * $page);
            $data_count = count($tmp);
            $meta = $this->populationServiceProvider->FetchManagementRag_page($request, $data_count);
            return array('meta' => $meta, 'data' => $tmp);
        } else {
            $page = $request->has('page') ? $request->get('page') : 1;
            $size = $request->has('size') ? $request->get('size') : 15;
            $paginated_response = 0;
            $data_count = 0;
            $meta = $this->populationServiceProvider->FetchManagementRag_page($request, $data_count);
            return ['meta' => $meta, 'data' => []];
        }
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/cohort-action-plans-history",
     *      operationId="historicalcohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Get list of Cohort Action Plan",
     *      description="Get list of Cohort Action Plan",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
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



    public function historiccohortactionplan(Request $request)
    {
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $filter = $this->actionPlanMeta->IsDataAvailableInYear($yearList, $filter);
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalCohortstudentActionPlans($filter, $yearList);
        $processedData = $this->actionPlanServiceProvider->ProcessCohortData($data, 'COHORT_ACTION_PLAN');
        return response()->json([
            "data" => $processedData
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/group-action-plans",
     *      operationId="groupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Get list of Group Action Plan",
     *      description="Get list of Group Action Plan",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
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

    public function currentgroupactionplan(Request $request)
    {
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $meta = $this->populationServiceProvider->Metadata($request);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $data = (array)$this->actionPlanServiceProvider->GroupActionPlans($filter, $meta, $yearList);
        $processedData = $this->actionPlanServiceProvider->ProcessGroupDatas($data, 'GROUP_ACTION_PLAN');
        $page = $request->has('page') ? $request->get('page') : 1;
        $size = $request->has('size') ? $request->get('size') : 15;
        $paginated_response = array_slice($processedData, ($page - 1) * $size, $size * $page);
        $fetchmeta = $this->actionPlanServiceProvider->FetchManagement_group($paginated_response, $meta);
        return response()->json([
            "data" => $paginated_response,
            "meta" => $fetchmeta
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/group-action-plans-history",
     *      operationId="historicalgroupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Get list of Historical Group Action Plan",
     *      description="Get list of Historical Group Action Plan",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/StudentActionResponses",example="StudentActionResponses")
     *             },
     *          )
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

    public function historicgroupactionplan(Request $request)
    {
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $filter = $this->actionPlanMeta->IsDataAvailableInYear($yearList, $filter);
        $filter = historyFilterByRound( $filter, [ 'sch', 'hs' ] );
        $data = $this->actionPlanServiceProvider->historicalGroupstudentActionPlans($filter, $yearList);
        $processedData = $this->actionPlanServiceProvider->ProcessGroupData($data, 'GROUP_ACTION_PLAN');
        return response()->json([
            "data" => $processedData
        ]);
    }

    public function commonstudentActionPlanDetail($request, $type, $id, $school_id)
    {
        $school_actions = [];
        if ($type == 'Cohort') {
            $rawdata = $this->rep_group_pdf->getacpdetail($id);
            $finalstring = $this->actionPlanServiceProvider->getFiltersApplied($request, '', TRUE, $school_id);
            if ($rawdata == null) {
                return response()->json([
                    "message" => $type . " Action Plan not Found"
                ], 404);
            }
        }
        if ($type == 'Group') {
            $rawdata = $this->rep_group_actionplan_model->getacpdetail($id);
            if ($rawdata == null) {
                return response()->json([
                    "message" => $type . " Action Plan not Found"
                ], 404);
            }
        }

        if (isset($rawdata) && !empty($rawdata)) {
            $filters = $rawdata['filter'];
            $final_filter = $this->actionPlanServiceProvider->Display_title_filters($filters);
            $final_filter = isset($final_filter) ? $final_filter : '';
            $risk = common_bias($rawdata['type_banc']);
            if ($risk == 'or' || $risk == 'hv' || $risk == 'sn' || $risk == 'sci' || $risk == 'ha')
                $riskType = "COMPOSITE_RISK";
            else
                $riskType = "POLAR_BIAS";
            $str_arr = explode(",", $rawdata['pop_id']);
            $names = $gender1 = $gender = [];
            if ($rawdata['pop_id'] != '') {
                foreach ($str_arr as $s) {
                    $r = $this->population_model->get($s);
                    if (isset($r['firstname'])) {
                        $names[] = $r['firstname'] . ' ' . $r['lastname'] . ',';
                        $gender1[] = $r['gender'];
                    }
                }
                // Group-Review -----START
                if (isset($rawdata['review']) || $rawdata['review'] == NULL) {
                    if(isJson($rawdata['review'])=='true'){
                        $review_data = json_decode($rawdata['review'], TRUE);
                        $final_review_array = $review_names_NIY = $review_names_POI = [];
                        foreach ($review_data as $ke=>$re) {
                            $r = $this->population_model->get($re['student_id']);
                            $review_names = $r['firstname'] . ' ' . $r['lastname'] . '';
                            $name_code = generateNameCode( (object)$r ?? null );
                            $final_review_array[] = [ "student_id" => $re['student_id'], "student_name" => $review_names, "review" => $re['review'],"name_code" => $name_code ];
                        }
                    }else{
                        $final_review_array = $rawdata['review'];
                    }
                    $final_review_array = isset($final_review_array)?$final_review_array:null;
                }
                // Group-Review -----END
                $gender = implode(",", $gender1);
                $names = rtrim(implode(" ", $names ?? []), ',');
            }
            $report_type = common_bias($rawdata['type_banc']);
            $statement = explode("~", $rawdata['statement']); //school-actions
            $question_filter_ids[] = "99999999999";
            foreach ($statement as $key => $statement_data) {
                if (isset($statement_data) && !empty($statement_data)) {
                    $explode_qcdata = explode("#", $statement_data);
                    if (isset($explode_qcdata[1])) {
                        $qc_values = json_decode($explode_qcdata[1]);
                        if (empty($qc_values) && ($type == 'Cohort')) {
                            $queIdArr[] = $explode_qcdata[0];
                            $cmtArr[$explode_qcdata[0]]['c1'] = $explode_qcdata[1];
                            $cmtArr[$explode_qcdata[0]]['c2'] = "";
                            $question_filter_ids = $queIdArr;
                            $get_detail = $cmtArr;
                        } else {
                            $queIdArr[] = $explode_qcdata[0];
                            $cmtArr[$explode_qcdata[0]]['c1'] = $qc_values[0];
                            $cmtArr[$explode_qcdata[0]]['c2'] = $qc_values[1];
                            $question_filter_ids = $queIdArr;
                            $get_detail = $cmtArr;
                        }
                    }
                }
            }
            $data['title_statement'] = $data['abbrev_statement'] = "";
            $final_data = [];
            if (isset($rawdata['section']) && !empty($rawdata['section'])) {
                $editabbr_section = array();

                if ($type == 'Cohort') {
                    $variant = 'IN_SCHOOL_COHORT';
                }else{
                    $variant = 'IN_SCHOOL_GROUP';
                }
                $selected_goals = explode (",", $rawdata['section']);
                $getstrsections = $this->question->getStrSections($report_type,$variant,$selected_goals);
                if (!$getstrsections->isEmpty()) {
                    foreach ($getstrsections as $strseckey => $strsecdata) {
                        $final_data['goals'][$strseckey]['id'] = $strsecdata['id'];
                        $final_data['goals'][$strseckey]['goal'] = $strsecdata['question'];
                        foreach ($question_filter_ids as $strseckey1 => $strsecdata1) {
                            $getstmtquestion = $this->question->getSelectedFiltersData($report_type,3, $question_filter_ids,$strsecdata['question']);
                        }
                        foreach ($getstmtquestion as $selfilkey => $selfildata) {
                            if (isset($selfildata) && !empty($selfildata)) {
                                $id_question = $selfildata['id'];
                                $question = $selfildata['question'];
                                $final_data['goals'][$strseckey]['signposts'][$selfilkey]['id'] = $id_question;
                                $final_data['goals'][$strseckey]['signposts'][$selfilkey]['signpost'] =  $question;
                            }
                        }
                    }
                }
                $final = [];
                if (isset($final_data['goals'])) {
                    foreach ($final_data['goals'] as $g) {
                        if (isset($g['signposts']) && !empty($g['signposts'])) {
                            $final[] =  $g;
                        }
                        $final = $final;
                    }
                } else {
                    $final = [];
                }
                $description = $this->actionPlanMeta->Custom_getDescription($rawdata);
                $feel = $description['feel'];
                $statement = $description['statement'];
                if ($type == 'Cohort') {
                    $title = $final_filter;
                    $formated_cohort_actionplan_signposts = $this->actionPlanServiceProvider->FormatStudentCohortActionPlanDetailed($rawdata, 'COHORT_ACTION_PLAN', $names, $final, $gender, $feel, $statement, $riskType, $title);
                    return $formated_cohort_actionplan_signposts;
                }
                if ($type == 'Group') {
                    $formated_group_actionplan_signposts = $this->actionPlanServiceProvider->FormatStudentGroupActionPlanDetailed($rawdata, 'GROUP_ACTION_PLAN', $names, $final, $description, $gender, $feel, $statement, $riskType,$final_review_array);
                    return $formated_group_actionplan_signposts;
                }
            }
        }
    }

    public function Commonsignposts_GroupandCohort($statement)
    {
        if ($statement == 'dl')
            $statement = 'sdl';
        if ($statement == 'dh')
            $statement = 'sdh';
        $get_title_statement = $this->str_groupbank_statements_model->getTititleStatement($statement);
        $getstrsections = $this->str_groupbank_sections->getStrSections($statement);
        if (isset($getstrsections) && !empty($getstrsections)) {
            $finalarray = [];
            foreach ($getstrsections as $strseckey => $strsecdata) {
                $finalarray[$strseckey]['id'] =  $strsecdata['id'];
                $finalarray[$strseckey]['goal'] =   $strsecdata['title_section'];
                $getstmtquestion = $this->str_groupbank_questions->getStmtQuestions($statement, $strsecdata['abbrev_section']);
                if (isset($getstmtquestion) && !empty($getstmtquestion)) {
                    foreach ($getstmtquestion as $stmt_que_key => $stmt_que_data) {
                        $stamtarr[$strseckey]['stmt_question'][$stmt_que_key] = $stmt_que_data['question'];
                        $finalarray[$strseckey]['signposts'][$stmt_que_key]['id'] = $stmt_que_data['id'];
                        $finalarray[$strseckey]['signposts'][$stmt_que_key]['question'] = $stmt_que_data['question'];
                        if (isset($getstrsections) && !empty($getstrsections)) {
                            $finalarray = [];
                            foreach ($getstrsections as $strseckey => $strsecdata) {
                                $finalarray[$strseckey]['id'] =  $strsecdata['id'];
                                $finalarray[$strseckey]['goal'] =   $strsecdata['title_section'];
                                $getstmtquestion = $this->str_groupbank_questions->getStmtQuestions($statement, $strsecdata['abbrev_section']);
                                if (isset($getstmtquestion) && !empty($getstmtquestion)) {
                                    foreach ($getstmtquestion as $stmt_que_key => $stmt_que_data) {
                                        $stamtarr[$strseckey]['stmt_question'][$stmt_que_key] = $stmt_que_data['question'];
                                        $finalarray[$strseckey]['signposts'][$stmt_que_key]['id'] = $stmt_que_data['id'];
                                        $finalarray[$strseckey]['signposts'][$stmt_que_key]['question'] = $stmt_que_data['question'];
                                    }
                                    $finalarray = [];
                                    foreach ($getstrsections as $strseckey => $strsecdata) {
                                        $finalarray[$strseckey]['id'] =  $strsecdata['id'];
                                        $finalarray[$strseckey]['goal'] =   trim($strsecdata['title_section']);
                                        $getstmtquestion = $this->str_groupbank_questions->getStmtQuestions($statement, $strsecdata['abbrev_section']);
                                        if (isset($getstmtquestion) && !empty($getstmtquestion)) {
                                            $t = 0;
                                            foreach ($getstmtquestion as $stmt_que_key => $stmt_que_data) {
                                                $t++;
                                                $stamtarr[$strseckey]['stmt_question'][$stmt_que_key] = $stmt_que_data['question'];
                                                $finalarray[$strseckey]['signposts'][$stmt_que_key]['id'] = $stmt_que_data['id'];
                                                $finalarray[$strseckey]['signposts'][$stmt_que_key]['signpost'] = trim($stmt_que_data['question']);
                                                if ($t == 5)
                                                    break;
                                            }
                                        }
                                    }
                                }
                                return response()->json(
                                    $finalarray
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/group-action-plans/{id}",
     *      operationId="detailgroupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Get Detail Group Action Plan",
     *      description="Get Detail Group Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/ActionPlanDetailResponses",example="ActionPlanDetailResponses")
     *             },
     *          )
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

    public function studentGroupActionPlanDetail(Request $request, $id)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $detailed_data_group = $this->commonstudentActionPlanDetail($request, 'Group', $id, $school_id);
        return $detailed_data_group;
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/cohort-action-plans/{id}",
     *      operationId="detailcohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Get Detail Cohort Action Plan",
     *      description="Get Detail Cohort Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
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
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/ActionPlanDetailResponses",example="ActionPlanDetailResponses")
     *             },
     *          )
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

    public function studentcohortActionPlanDetail(Request $request, $id)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $detailed_data_cohort = $this->commonstudentActionPlanDetail($request, 'Cohort', $id, $school_id);
        return $detailed_data_cohort;
    }

    /**
     * @OA\Post(
     *      path="/api-astnext/group-action-plans",
     *      operationId="CreateGroupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Create Group Action Plan",
     *      description="Create Group Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              required={"filters", "student_id","reason_ids", "goal_ids", "signpost_ids", "school_action", "lead", "review_date"},
     *              @OA\Property(
     *                  property="student_id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="filters",
     *                  type="array",
     *                  @OA\Items(type="object"),
     *                  description="array of objects"
     *              ),
     *              @OA\Property(property="bias", type="string", example="18"),
     *              @OA\Property(
     *                  property="reason_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="goal_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="signpost_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(property="school_action", type="string", example="Albert is XYZ"),
     *              @OA\Property(property="lead", type="string", description="Who is Responsible"),
     *              @OA\Property(property="review_date", type="date"),
     *           ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function creategroupactionplan(Request $request)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $bias = GetBiasInAbbrev_custom($request->risk);
        $filters = $this->actionPlanServiceProvider->getFiltersAppliedonCreate($request->filters, $school_id);
        $filters_applied = isset($filters['final_string']) ? $filters['final_string'] : '';
        $signpost = $request->signpost_ids;
        if (isset($request->signpost_ids)) {
            foreach ($signpost as $value) {
                $combine_stmts[] = $value . '#["",""]~';
            }
            $combine_stmts = implode("", $combine_stmts);
        } else {
            $combine_stmts = '';
        }
        $school_actions = $request->school_action;
        if (count($request->student_ids) > 1){
            // Group-Review -----START
            $counter = 0;
            foreach($request->student_ids as $key=>$value){
                $review_arr[$counter]['student_id'] = $value;
                $review_arr[$counter]['review'] = NULL;
                $counter++;
            }
            // Group-Review -----END
            $request->student_ids = rtrim(implode(",", $request->student_ids));
        }
        else{
            $request->student_ids = implode("", $request->student_ids);
        }
        $postdata = $this->actionPlanMeta->CommitGroupaction($request, $combine_stmts, $bias, $filters_applied,$review_arr);
        return response()->json(['message' => $postdata['message'], 'id' => $postdata['id']], $postdata['status']);
    }

    /**
     * @OA\Post(
     *      path="/api-astnext/cohort-action-plans",
     *      operationId="CreateCohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Create Cohort Action Plan",
     *      description="Create Cohort Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              required={"student_id","reason_ids", "goal_ids", "signpost_ids", "school_action", "lead", "review_date"},
     *              @OA\Property(
     *                  property="student_id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="filters",
     *                  type="array",
     *                  @OA\Items(type="object"),
     *                  description="array of objects"
     *              ),
     *              @OA\Property(property="bias", type="string", example="18"),
     *              @OA\Property(
     *                  property="reason_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="goal_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(
     *                  property="signpost_ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  description="array of ids"
     *              ),
     *              @OA\Property(property="school_action", type="string", example="Albert is XYZ"),
     *              @OA\Property(property="lead", type="string", description="Who is Responsible"),
     *              @OA\Property(property="review_date", type="date"),
     *           ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function createcohortactionplan(Request $request)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $bias = GetBiasInAbbrev_custom($request->risk);
        $filters = $this->actionPlanServiceProvider->getFiltersAppliedonCreate($request->filters, $school_id);
        $filters_applied = isset($filters['final_string']) ? $filters['final_string'] : '';
        $signpost = $request->signpost_ids;
        $school_actions = $request->school_action;
        if (isset($request->signpost_ids)) {
            foreach ($signpost as $value) {
                $combine_stmts[] = $value . '#["",""]~';
            }
            $combine_stmts = implode("", $combine_stmts);
        } else {
            $combine_stmts = '';
        }
        if (count($request->student_ids) > 1)
            $request->student_ids = rtrim(implode(",", $request->student_ids));
        else
            $request->student_ids = implode("", $request->student_ids);
        $postdata = $this->actionPlanMeta->CommitCohortaction($request, $combine_stmts, $bias, $filters_applied);
        return response()->json(['message' => $postdata['message'], 'id' => $postdata['id']], $postdata['status']);
    }

    public function fetchCohortGoals(Request $request)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $bias_name = $this->actionPlanMeta->biasName($request->get('risk'));
        $statement = GetBiasInAbbrevforGroup($bias_name);
        $finalarray = $this->Commonsignposts_GroupandCohort($statement);
        return $finalarray;
    }

    /**
     * @OA\Delete(
     *      path="/api-astnext/student-action-plans/{id}",
     *      operationId="DeleteStudentActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Delete Student Action Plan",
     *      description="Delete Student Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function deleteStudentActionplan(Request $request, $id)
    {
        $this->actionPlanMeta->deleteStudentActionplan($request, $id);
        return response()->json(["message" => "Action Plan deleted Successfully"], 200);
    }

    /**
     * @OA\Delete(
     *      path="/api-astnext/family-signposts/{id}",
     *      operationId="DeleteFamilySignPost",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Delete Family SignPost",
     *      description="Delete Family SignPost",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function deleteFamilySignpost(Request $request, $id)
    {
        $this->actionPlanMeta->deleteFamilysignpost($request, $id);
        return response()->json(["message" => "Action Plan deleted Successfully"], 200);
    }

    /**
     * @OA\Patch(
     *      path="/api-astnext/student-action-plans/{id}/review",
     *      operationId="ReviewStudentActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Action Plans"},
     *      summary="Update Action Plan Impact Update",
     *      description="Update Action Plan Impact Update",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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


    public function updateStudentActionplan(Request $request, $id)
    {
        $this->actionPlanMeta->updateStudentActionplan($request, $id);
        return $this->studentActionPlanDetail($request, $id);
        //return response()->json([ "message" => "Action Plan updated Successfully"], 200);
    }

    /**
     * @OA\Patch(
     *      path="/api-astnext/family-signposts/{id}/review",
     *      operationId="ReviewFamilySignPost",
     *      security={{"bearer_token":{} }},
     *      tags={"Family SignPost"},
     *      summary="Update Family SignPost Impact Update",
     *      description="Update Family SignPost Impact Update",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function updateFamilySignpost(Request $request, $id)
    {
        $this->actionPlanMeta->updateFamilySignpost($request, $id);
        return $this->familySignPostDetail($request, $id);
        //return response()->json([ "message" => "Action Plan updated Successfully"], 200);
    }

    /**
     * @OA\Delete(
     *      path="/api-astnext/group-action-plans/{id}",
     *      operationId="DeleteGroupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Delete Group Action Plan",
     *      description="Delete Group Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function deleteGroupActionplan(Request $request, $id)
    {
        $this->actionPlanMeta->deleteGroupActionplan($request, $id);
        return response()->json(["message" => "Group Action Plan deleted Successfully"], 200);
    }


    /**
     * @OA\Delete(
     *      path="/api-astnext/cohort-action-plans/{id}",
     *      operationId="DeleteCohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Delete Cohort Action Plan",
     *      description="Delete Cohort Action Plan",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function deleteCohortActionplan(Request $request, $id)
    {
        $this->actionPlanMeta->deleteCohortActionplan($request, $id);
        return response()->json(["message" => "Cohort Action Plan deleted Successfully"], 200);
    }

    /**
     * @OA\Patch(
     *      path="/api-astnext/group-action-plans/{id}/review",
     *      operationId="ReviewGroupActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Group Action Plans"},
     *      summary="Update Group mAction Plan Impact Update",
     *      description="Update Group Action Plan Impact Update",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function updateGroupActionplan(Request $request, $id)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $this->actionPlanMeta->updateGroupActionplan($request, $id);
        return $this->commonstudentActionPlanDetail($request, 'Group', $id, $school_id);
    }


    /**
     * @OA\Patch(
     *      path="/api-astnext/cohort-action-plans/{id}/review",
     *      operationId="ReviewCohortActionPlan",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort Action Plans"},
     *      summary="Update Cohort mAction Plan Impact Update",
     *      description="Update Cohort Action Plan Impact Update",
     *      @OA\Parameter(
     *         ref="#/components/parameters/Id",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
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

    public function updateCohortActionplan(Request $request, $id)
    {
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $this->actionPlanMeta->updateCohortActionplan($request, $id);
        return $this->commonstudentActionPlanDetail($request, 'Cohort', $id, $school_id);
    }
}
