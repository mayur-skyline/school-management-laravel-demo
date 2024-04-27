<?php

namespace App\Http\Controllers\AstNext;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AstNextServiceProvider;
use App\Services\AstNextAssessmentDataServiceProvider;
use App\Services\CohortDataFilterServiceProvider;
use App\Services\PopulationServiceProvider;
use App\Services\RedisServiceProvider;
use App\Services\AssessmentTrackerSummaryServiceProvider;
use App\Http\Requests\RoundRequest;
use App\Models\Dbschools\Model_arr_year;
use App\Models\Dbglobal\Model_dat_schools;
use App\Util\Grouping\RoundManagement\Round;
use App\Models\Dbschools\Model_population;

/**
 *   @OA\Parameter(
 *      parameter="assessmenttype",
 *      in="query",
 *      required = true,
 *      name="type",
 *      description="COMPLETED | INCOMPLETE | MANIPULATED | NOT_STARTED",
 *      @OA\Schema(
 *          type="string"
 *      ),
 *   )
 */
class AstNextAssessmentController extends Controller
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->astNextServiceAssessmentDataServiceProvider = new AstNextAssessmentDataServiceProvider();
        $this->populationServiceProvider = new PopulationServiceProvider();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->redisServiceProvider = new RedisServiceProvider();
        $this->trackerSummary = new AssessmentTrackerSummaryServiceProvider();
        $this->arrYear = new Model_arr_year(); 
        $this->dat_school = new Model_dat_schools();
        $this->roundValue = new Round();
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/pupil-assessment-responses",
     *      operationId="PupilAssessmentResponses",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Get Student Assessment Responses",
     *      description="Get Student Assessment Responses",
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
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/FilterResponses",example="FilterResponses")
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
    public function pupilAssessmentInformationData(Request $request)
    {
        $school_id = $request->get('school_id');
        $pupil_id = $request->get('pupil_id');
        $request->request->add(['api', true]);

        //$academicYear = $this->astNextServiceProvider->GetAcademicYear($school_id);
        $year = IsDataAvailableInYearStudent($school_id, $pupil_id);

        if( $year == null )
            abort(400, 'Data not available');
        $request->request->add(['year' => $year]);
        $result = $this->astNextServiceAssessmentDataServiceProvider->AssessmentData($request);
        if ($result == null) {
            return response()->json([
                "message" => "Assessment information not Found"
            ], 404);
        } else {
            return response()->json([
                'responses' => [
                    'OUT_OF_SCHOOL' => $result['out_of_school'],
                    'IN_SCHOOL' => $result['in_school']
                ],
            ]);
        }
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/assessment-tracker",
     *      operationId="PupilAssessmentTracker",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Get Student Assessment Responses",
     *      description="Get Student Assessment Responses",
     *      @OA\Parameter(
     *         ref="#/components/parameters/keyword",
     *      ),
     *      @OA\Parameter(
     *         ref="#/components/parameters/assessmenttype",
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

    public function AssessmentTracker(Request $request, $type)
    {
        //Based on Filter get pupil in the category
        $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
        //Fetch from redis
        //$type = $this->populationServiceProvider->RequestType($request);
        $meta = $this->populationServiceProvider->Metadata($request);
        //$result = $this->redisServiceProvider->getRecord($type, $request->get('keyword'), $meta['page'], $request->get('school_id'), $filter);
        // if ($result) {
        //     return response()->json([
        //         "data" => $result['data'],
        //         "meta" => $result['meta']
        //     ]);
        // }

        $result = $this->populationServiceProvider->getPupils($filter, $request, $type);
        if (isset($result['data'])) {
            return response()->json([
                "data" => mb_convert_encoding( $result['data'], 'UTF-8', 'UTF-8' ),
                "meta" => $result['meta']
            ]);
        } else {
            return response()->json([
                "message" => isset($result['message']) ? $result['message'] : 'Not Message'
            ], 400);
        }
    }

    public function AssessmentTrackerSummary( Request $request )
    {
        return $this->trackerSummary->summary( $request );
    }

    public function startRound(RoundRequest $request)
    {
        return $this->trackerSummary->updateRound($request, 'in_progress');
    }

    public function sendbulkmail( Request $request )
    {
        try{
            if( !$request->has('type') )
            return response()->json([ 'message' => 'Unknown Request Type'], 400);
            $type = $request->get('type');
            $school_id = $request->get('school_id');
            $filter = $this->CohortDataFilterServiceProvider->AssessmentFilters($request);
            $filter['academic_year'][0]  = $this->dat_school->SchoolAcademicYear($school_id);
            $filter['round'][0] = $this->roundValue->getInProgressRoundForSpecCampus( $school_id, $filter['academic_year'][0], $filter['campus'][0] ?? 'No Campus' );
            $data = $this->populationServiceProvider->fetchpupilforEmail( $request, $filter, $type );
            if( count( $data) == 0 ) {
                return response()->json(['message' => 'No student is found on the selected filter' ],404);
            }
            $list = array_filter($data->toArray(), function($key) use ($data) {
                return $data[$key]['email_address'] != null && $data[$key]['email_address'] != '';
            }, ARRAY_FILTER_USE_KEY);
            if( count( $list ) == 0 ) {
                return response()->json(['message' => 'None of the selected students have email addresses assigned. Are you sure your school has imported them?' ],404);
            }
            return response()->json(['message' => 'Email Sent', 'count' => count($list) ]);
        }catch(\Exception $ex) {
            response()->json(['message' => 'Something went wrong' ],500);
        }
        
    }

    public function showCampus( $year ) {
        return $this->arrYear->getCampusData( $year );
    }

}
