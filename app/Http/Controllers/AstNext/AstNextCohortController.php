<?php

namespace App\Http\Controllers\AstNext;

use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AstNextServiceProvider;
use App\Services\AstNextAssessmentDataServiceProvider;
use App\Services\CohortDataFilterServiceProvider;
use App\Services\CohortServiceProvider;
use App\Services\PopulationServiceProvider;
use App\Services\RedisServiceProvider;
use App\Models\Dbschools\Model_school_table_exist;
use App\Services\AstNextPupilCohortServiceProvider;
use DB;

class AstNextCohortController extends Controller
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->astNextServiceAssessmentDataServiceProvider = new AstNextAssessmentDataServiceProvider();
        $this->populationServiceProvider = new PopulationServiceProvider();
        $this->CohortDataFilterServiceProvider = new CohortDataFilterServiceProvider();
        $this->redisServiceProvider = new RedisServiceProvider();
        $this->cohortServiceProvider = new CohortServiceProvider();
        $this->schoolTableExist_model = new Model_school_table_exist();
        $this->pupilCohortServiceProvider = new AstNextPupilCohortServiceProvider();
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/pupil-cohort-data",
     *      operationId="PupilCohortData",
     *      security={{"bearer_token":{} }},
     *      tags={"Cohort"},
     *      summary="Specific Student Cohort Information",
     *      description="Specific Student Cohort Information",
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
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              examples= {
     *                "ex": @OA\Schema(ref="#/components/examples/404Responses",example="404Responses")
     *             },
     *          )
     *      ),
     *  )
     */
    public function pupilCohortData(Request $request)
    {
        $school_id = $request->get('school_id');
        $pupil_id = $request->get('pupil_id');
        $year = IsDataAvailableInYearStudent($school_id, $pupil_id);
        if( $year == null )
            abort(400, 'Data not available');
        $pupil_data = $this->astNextServiceProvider->getPupilInformation($pupil_id);
        $dbname = getSchoolDatabase($school_id);
        return $this->pupilCohortServiceProvider->process( $request, $pupil_id, $pupil_data );
    }


}
