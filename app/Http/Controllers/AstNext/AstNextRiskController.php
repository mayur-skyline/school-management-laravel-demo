<?php

namespace App\Http\Controllers\AstNext;

use App\Http\Controllers\Controller;
use App\Models\Dbglobal\Model_str_bank_questions;
use App\Models\Dbglobal\Model_str_bank_questions_ui_ux;
use App\Models\Dbglobal\Model_str_bank_sections;
use App\Models\Dbschools\Model_population;
use Illuminate\Http\Request;
use App\Services\AstNextServiceProvider;
use App\Services\AstNextRiskServiceProvider;
use App\Services\ActionPlanMetaServiceProvider;
use App\Services\AstNextActionPlanStatementServiceProvider;
use App\Services\AstNextCommonServiceProvider;
use App\Services\CohortServiceProvider;
use App\Models\Dbschools\Model_school_table_exist;
use App\Util\Grouping\Composite\Composite;
use DB;

class AstNextRiskController extends Controller
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->astNextServiceRiskProvider = new AstNextRiskServiceProvider();
        $this->actionPlanMeta = new ActionPlanMetaServiceProvider();
        $this->actionStatement = new AstNextActionPlanStatementServiceProvider();
        $this->common = new AstNextCommonServiceProvider();
        $this->cohortServiceProvider = new CohortServiceProvider();
        $this->schoolTableExist_model = new Model_school_table_exist();
        $this->str_bank_section = new Model_str_bank_sections();
        //$this->str_bank_question = new Model_str_bank_questions();
        $this->str_bank_question = new Model_str_bank_questions_ui_ux();
        $this->pop = new Model_population();
        $this->composite = new Composite();
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/pupil-risk-cohort-data",
     *      operationId="PupilRiskCohortData",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort"},
     *      summary="Get Pupil Risk Information",
     *      description="Get Pupil Risk Information",
     *      @OA\Parameter(
     *         ref="#/components/parameters/pupilId",
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
     *      ),
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
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */

    public function pupilRiskDescriptionData(Request $request)
    {
        $school_id = $request->get('school_id');
        $pupil_id = $request->get('pupil_id');
        $request->request->add(['api', true]);
        $round = $request->get('assessment_round');
        //$academicYear = $this->astNextServiceProvider->GetAcademicYear($school_id);
        $year = $request->get('academic_year'); //IsDataAvailableInYearStudent($school_id, $pupil_id);

        if( $year == null )
            abort(400, 'Data not available');
        $request->request->add(['year' => $year]);
        //$result = $this->astNextServiceRiskProvider->riskDdescriptors($request);
        $result = $this->astNextServiceRiskProvider->newriskStatement( $request, $year, $round );
        if ($result == null) {
            return response()->json([
                "message" => "Risk not Found"
            ], 404);
        } else {
            return response()->json([
                'risks' => $result
            ]);
        }
    }

    public function StudentRiskCommon($request, $id, $type) {
        $student_id = $id;
        $school_id = $request->get('school_id');
        $assessment_type = $type == 1 ? 'OUT_OF_SCHOOL' : 'IN_SCHOOL';
        $yearList = $this->actionPlanMeta->academicYearsList($request->get('school_id'));
        $data = $this->actionStatement->FetchStudentLastScore($yearList, $type, $student_id, $request);
        $rawdata = $this->actionStatement->FetchStudentLastScorerawData($yearList, $type, $student_id, $request);
        $rawdata = RawDataArray($rawdata);
        list( 'risks' => $risks ) = $this->composite->StudentCompositeRisksObject((object)$data['score'], $rawdata, $assessment_type, []);
        $other_composite_biases = array_column( $risks, 'type' );
        if( $assessment_type == "IN_SCHOOL" ) {
            //get OUT SCHOOL
            $second_school_data_object = $this->actionStatement->FetchStudentLastScore($yearList, 1, $student_id, $request);
            $second_rawdata = $this->actionStatement->FetchStudentLastScorerawData($yearList, 1, $student_id, $request);
            if( $second_rawdata && isset($second_school_data_object['score']) ){
                $second_rawdata = RawDataArray($second_rawdata);
                list( 'risks' => $risks ) = $this->composite->StudentSCICompositeRisksObject( (object)$data['score'], (object)$second_school_data_object['score'], $rawdata, $second_rawdata, [] );
            }
        }
        else {
            //get IN SCHOOL
            $second_school_data_object = $this->actionStatement->FetchStudentLastScore($yearList, 3, $student_id, $request);
            $second_rawdata = $this->actionStatement->FetchStudentLastScorerawData($yearList, 3, $student_id, $request);
            if($second_rawdata && isset($second_school_data_object['score']) ){
                $second_rawdata = RawDataArray($second_rawdata);
                list( 'risks' => $risks ) = $this->composite->StudentSCICompositeRisksObject( (object)$second_school_data_object['score'], (object)$data['score'], $second_rawdata, $rawdata, [] );
            }
        }
        $sci_composite_biases = array_column( $risks, 'type' );
        $composite_biases = array_merge( $other_composite_biases, $sci_composite_biases );
        $composite_biases = array_values( array_unique( $composite_biases ) );
        
        if($data == null) {
            abort(404,"Student has no Assessment Data");
        }
        $composite_risks = $this->common->CompositeListRisk2( $composite_biases );
        return array('risks' => $data['risks'], 'composite_risks' => $composite_risks, 'score' => $data['score'] );
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/students/{id}/risks",
     *      operationId="StudentRisk",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Risks"},
     *      summary="Get all risk associated to a student",
     *      description="Get all risk associated to a student",
     *      @OA\Parameter(
     *        name="id",
     *        description="Student Id",
     *        required=true,
     *        in="path",
     *        @OA\Schema(
     *           type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/type",
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
     *      ),
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
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */


    public function StudentRisks(Request $request, $id) {
        $type = 1;
        if($request->has('type'))
            $type = $request->get('type') == 'IN_SCHOOL' ? 3 : 1;
        $data = $this->StudentRiskCommon($request, $id, $type);
        return response()->json([
            "polar_biases" =>  $data['risks'],
            "composite_risks" => $data['composite_risks']
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/risks",
     *      operationId="AllRisk",
     *      security={{"bearer_token":{} }},
     *      tags={"Risks"},
     *      summary="Get all risks",
     *      description="Get all risks",
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolId",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/schoolCode",
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
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
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */



    public function risklist() {
        $polar_risks = $this->common->allPolarBiasList();
        $composite_risks = $this->common->allCompositeListRisk();
        return response()->json([
            "polar_biases" =>  $polar_risks,
            "composite_risks" => $composite_risks
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/students/{id}/future-risks",
     *      operationId="futureRisk",
     *      security={{"bearer_token":{} }},
     *      tags={"Future Risks"},
     *      summary="Get all future risks",
     *      description="Get all future risks",
     *      @OA\Parameter(
     *        name="id",
     *        description="Student Id",
     *        required=true,
     *        in="path",
     *        @OA\Schema(
     *           type="integer"
     *        )
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
     *      ),
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
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */
    public function getRiskSection(Request $request, $id) {
       $data = $this->astNextServiceRiskProvider->getRiskSection($request, $id);
       return response()->json($data);
    }

    public function getgroupStudentRisks(Request $request) {
        $student_ids = $request->get('ids');
        $school_id = $request->get('school_id');
        $final_polar_biases = $final_composite_biases = [];
        foreach($student_ids as $student_pop_ids){
            $data[$student_pop_ids] = $this->StudentRiskCommon($request, $student_pop_ids, 3);
            $actionPlans[$student_pop_ids] = $this->actionPlanMeta->getActionPlanAfterAssessmentGroup($data[$student_pop_ids]['score'], $student_pop_ids);
            $response[$student_pop_ids] = FilterActionPlanRisk($data[$student_pop_ids], $actionPlans[$student_pop_ids], 3);
        }
        if(!empty($response) && count($response)>=2){
            $total_students = count($student_ids);
            $response = find_common_biases($response,$total_students);
            $final_polar_biases = isset($response['polar_biases'])?$response['polar_biases']:[];
            $final_composite_biases = isset($response['composite_risks'])?$response['composite_risks']:[];
            if(!empty($final_polar_biases) || !empty($final_composite_biases )){
                return array(
                    "polar_biases" =>  $final_polar_biases,
                    "composite_risks" =>  $final_composite_biases
                );
            }else{
                return response()->json([
                    "message" => "These students do not all share a common risk! Did you select the right students?"
                ], 404);
            }
        }else{
            return response()->json([
                "message" => "These students do not share a common risk! Did you select the right students?"
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/students/{id}/filtered-action-plan-risks",
     *      operationId="FilteredRisk",
     *      security={{"bearer_token":{} }},
     *      tags={"Student Risks"},
     *      summary="Get all student risks, without action plan",
     *      description="Get all student risks, without action plan",
     *      @OA\Parameter(
     *        name="id",
     *        description="Student Id",
     *        required=true,
     *        in="path",
     *        @OA\Schema(
     *           type="integer"
     *        )
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/type",
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
     *      ),
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
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *           @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */
    public function getFilteredActionPlanRiskSection(Request $request, $id) {
        $type = $request->get('type');
        if($type == 'STUDENT_ACTION_PLAN') {
            $data = $this->StudentRiskCommon($request, $id, 3);
            $actionPlans = $this->actionPlanMeta->getActionPlanAfterAssessment($data['score'], $id);
        }else if($type == 'FAMILY_SIGNPOST'){
            $data = $this->StudentRiskCommon($request, $id, 1);
            $actionPlans = $this->actionPlanMeta->getActionPlanAfterAssessmentFamilySignPost($data['score'], $id);
        }else if($type == 'MONITOR_COMMENT') {
            $data = $this->StudentRiskCommon($request, $id, 3);
            $actionPlans = $this->actionPlanMeta->getActionPlanAfterAssessmentMonitorComment($data['score'], $id);
        }else {
            abort(400, 'Action Type is Unknown');
        }
        $response = FilterActionPlanRisk($data, $actionPlans, $type);
        return response()->json($response);

    }
}
