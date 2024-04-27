<?php

namespace App\Http\Controllers\AstNext;

use App\Http\Controllers\Controller;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_monitor_comments;
use Illuminate\Http\Request;
use App\Services\CohortDataFilterServiceProvider;
use App\Services\PopulationServiceProvider;
use App\Services\ActionPlanMetaServiceProvider;
use App\Services\ActionPlanServiceProvider;
use App\Services\MonitorCommentServiceProvider;

class AstNextMonitorCommentController extends Controller
{
    public function __construct()
    {
        $this->monitorComment = new MonitorCommentServiceProvider();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->populationServiceProvider = new PopulationServiceProvider();
        $this->actionPlanMeta = new ActionPlanMetaServiceProvider();
        $this->actionPlanServiceProvider = new ActionPlanServiceProvider();
        $this->datSchools_model = new Model_dat_schools();
    }


    /**
     * @OA\Get(
     *      path="/api-astnext/monitor-comments",
     *      operationId="monitorComments",
     *      security={{"bearer_token":{} }},
     *      tags={"Monitor Comments"},
     *      summary="Get list of Monitor Comments",
     *      description="Get list of Monitor Comments",
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


    public function getMonitorComments(Request $request)
    {

        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        $filter = $this->populationServiceProvider->setDefaultFilter($filter, $request->get('school_id'));
        $meta = $this->populationServiceProvider->Metadata($request);
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $data = $this->actionPlanServiceProvider->monitorComment($filter, $meta, $yearList);
        $data = paginate( $request->get('size'), $request->get('page'), count( $data ), $data );
        $processedData = $this->monitorComment->FormatAllMonitorComment($data, 'MONITOR_COMMENT');
        $fetchmeta = $this->actionPlanServiceProvider->FetchManagement($data, $meta);
        return response()->json([
            "data" => $processedData,
            "meta" => $fetchmeta
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/monitor-comments-history",
     *      operationId="historymonitorComments",
     *      security={{"bearer_token":{} }},
     *      tags={"Monitor Comments"},
     *      summary="Get list of Historical Monitor Comments",
     *      description="Get list of Historical Monitor Comments",
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



    public function getHistoricalMonitorComments(Request $request){
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $iyear = $this->datSchools_model->SchoolAcademicYear($school_id);
        $filter = FullQueryfilter($request, $iyear);
        $meta = Metadata($request);
        $years = academicYearsList($school_id);
        $data = $this->monitorComment->getPupilWithHistoricalMonitorComment($filter, $years);
        return response()->json(["data" => $data]);
    }


    /**
     * @OA\Post(
     *      path="/api-astnext/monitor-comments",
     *      operationId="CreateMonitorComment",
     *      security={{"bearer_token":{} }},
     *      tags={"Monitor Comments"},
     *      summary="Create Monitor Comment",
     *      description="Create Monitor Comment",
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
     *              required={"student_id","risk", "comment", "review_comment"},
     *              @OA\Property(
     *                  property="student_id",
     *                  type="integer"
     *               ),
     *              @OA\Property(property="risk", type="string", example="POLAR_LOW_SELF_DISCLOSURE"),
     *              @OA\Property(property="comment", type="string", example="Albert is XYZ"),
     *              @OA\Property(property="review_comment", type="string"),
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

    public function create_monitor_comment(Request $request)
    {
        $monitor_comments = $this->monitorComment->addMonitorComment($request);
        return response()->json([
            'id' => $monitor_comments->id,
            'message' => 'New Monitor Comment Created Successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/monitor-comments/{id}",
     *      operationId="detailmonitorComments",
     *      security={{"bearer_token":{} }},
     *      tags={"Monitor Comments"},
     *      summary="Get Details Monitor Comment",
     *      description="Get Details Monitor Comment",
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

    public function monitor_comment(Request $request, $id)
    {
        $data = $this->monitorComment->singleMonitorComment($id);
        return response()->json($data);
    }

    /**
     * @OA\Delete(
     *      path="/api-astnext/monitor-comments/{id}",
     *      operationId="deletemonitorComments",
     *      security={{"bearer_token":{} }},
     *      tags={"Monitor Comments"},
     *      summary="Remove Monitor Comment",
     *      description="Remove Monitor Comment",
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


    public function delete_monitor_comment(Request $request, $id)
    {
        $data = $this->monitorComment->deleteMonitorComment($id);
        return response()->json($data);
    }

}
