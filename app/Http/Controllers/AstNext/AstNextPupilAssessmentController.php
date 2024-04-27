<?php
namespace App\Http\Controllers\AstNext;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AstNextServiceProvider;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_arr_year;
use App\Services\AssessmentServiceProvider;
use App\Libraries\Encdec;
use App\Models\Dbglobal\Model_otp;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_ass_rawdata;
use App\Models\Dbschools\Model_ass_score;
use App\Models\Dbschools\Model_ass_tracking;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_school_table_exist;
use App\Services\AstNextPupilAssessmentProvider;
use App\Util\Builder\AstNextResources\ResourcesBuilder;
use App\Services\AstNextuiuxAssessmentProvider;

use function PHPSTORM_META\map;

class AstNextPupilAssessmentController extends Controller
{
    public function __construct()
    {
        $this->astNextServiceProvider = new AstNextServiceProvider();
        $this->datSchools_model = new Model_dat_schools();
        $this->arrYear_model = new Model_arr_year();
        $this->encdec = new Encdec();
        $this->population_model = new Model_population();
        $this->AstNextPupilAssessmentProvider = new AstNextPupilAssessmentProvider();
        $this->assessmentServiceProvider = new AssessmentServiceProvider();
        $this->ass_rawdata_model = new Model_ass_rawdata();
        $this->ass_main_model = new Model_ass_main();
        $this->ass_tracking_model = new Model_ass_tracking();
        $this->ass_score_model = new Model_ass_score();
        $this->schoolTableExist_model = new Model_school_table_exist();
        $this->ResourcesBuilder = new ResourcesBuilder();
        $this->AstNextuiuxAssessmentProvider = new AstNextuiuxAssessmentProvider();
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/year-lists",
     *      operationId="yearLists",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Get List of Years",
     *      description="Get List of Years",
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
    public function yearList()
    {
        return response()->json([
            "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13"
        ]);
    }
    /**
         * @OA\Get(
         *      path="/api-astnext/year-grouping-lists",
         *      operationId="yearGroupLists",
         *      security={{"bearer_token":{} }},
         *      tags={"Assessment"},
         *      summary="Get Year Groups",
         *      description="Get Year Groups",
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
    public function yearGroupingList()
    {
        return response()->json([
            "3-6", "7-10", "11-13"
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/pupil-year-group",
     *      operationId="pupilYearGroup",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Get Pupil Under Year Group",
     *      description="Get Pupil Under Year Group",
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

    public function getPupilOnYearGroup(Request $request,$year_group)
    {
        $request->request->add(['year_group' => $year_group]);
        $school_id = $request->get('school_id');
        dbSchool($school_id);
        $year_group = $request->has('year_group') ? (int)$request->get('year_group') : 3;
        $groups = [];
        if ($year_group >= 3 && $year_group <= 6) {
            $groups = [3, 4, 5, 6];
        } else if ($year_group >= 7  && $year_group <= 10) {
            $groups = [7, 8, 9, 10];
        } else {
            $groups = [11, 12, 13];
        }
        $getPupilPerYearGroup = Model_population::select('id', 'firstname', 'lastname', 'gender')->where('firstname', 'LIKE', 'test%')->whereIn('lastname', $groups)->get();
        $curr = date('Ymd');
        $result = '';
        if (isset($school_id) && !empty($school_id)) {
            $detail = $this->datSchools_model->SchoolAcademicYear($school_id);
            $result = $detail;
        }
        $academicyear = $request->has('academic_year') ? $request->get('academic_year') : $result;
        $data = [];
        foreach ($getPupilPerYearGroup as $pupil) {
            $check_exists = $this->ass_rawdata_model->checkAssForSameDay($pupil->id, $curr, $academicyear);
            if ($check_exists == FALSE) {
                //assessment run----START
                $is_trail = true;
                if (isset($school_id) && !empty($school_id)) {
                    $detail = $this->datSchools_model->SchoolAcademicYear($school_id);
                    $result = $detail;
                }
                $user_id = $pupil->id;
                $myGender = $this->population_model->get($user_id);
                $myGender = $myGender['gender'];
                $myAcademicYear = $result;
                $myYear = $this->arrYear_model->getPupilYear($myAcademicYear, $user_id);
                $this->platform = Config('constants.platforms.astracking');
                $platform =  '';
                if ($myYear) {
                    if ($this->platform == "footprint") {
                        $platform = "Footprint";
                    } elseif ($this->platform == "ast") {
                        if ($myYear <= 8) {
                            $platform = "ast1";
                        } else {
                            $platform = "ast2";
                        }
                    } elseif ($this->platform == "cas") {
                        if ($myYear <= 8) {
                            $platform = "cas1";
                        } else {
                            $platform = "cas2";
                        }
                    }
                }
                $platform_type = $platform;
                $ass_data = array(
                    "school_id" => $school_id,
                    "user_id" => $user_id,
                    "gender" => $myGender,
                    "my_year" => $myYear,
                    "my_academic_year" => $myAcademicYear,
                    "platform_type" => $platform_type,
                );
                $data_ass = $this->AstNextuiuxAssessmentProvider->getAssessmentAudioAndIntro($ass_data,$request,true);
                $pupil['year_group']= (int) $pupil->lastname;
                $pupil['name']= $pupil->firstname." ".$pupil->lastname;
                if(isset($data_ass) && !empty($data_ass))
                    $pupil['exists'] = true;
                else
                    $pupil['exists'] = false;
                //assessment run----END
                $data[] = $pupil;
            }
        }
        return response()->json([
            "data" => $data
        ]);
    }
    /**
     * @OA\Get(
     *      path="/api-astnext/basic-information",
     *      operationId="basicInformation",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Get Pupil BASIC Information",
     *      description="Get Pupil BASIC Information",
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
    public function basicInformation(Request $request,$id)
    {
        $pupil_id = $id;
        $data =  $this->astNextServiceProvider->getPupilInformation($pupil_id);
        return response()->json($data);
    }

    public function getTrainingCourse(Request $request)
    {
        $school_id = $request->get('school_id');
        return staticnameandlinks($school_id,$request);
    }

    /**
     * @OA\Get(
     *      path="/api-astnext/delete-assessment/{id}",
     *      operationId="deleteAssessment",
     *      security={{"bearer_token":{} }},
     *      tags={"Assessment"},
     *      summary="Delete Assessment",
     *      description="Delete Assessment",
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
    public function delete_assessment(Request $request, $id){
        $data = $this->AstNextPupilAssessmentProvider->delete($request, $id);
        return $data;
    }

    public function export_students(Request $request)
    {
        $data = $this->AstNextPupilAssessmentProvider->exportStudentWithAssessmentType($request);
        //return response()->json($data);
        return $data;
    }

    public function getTrainingvidsandtexts(Request $request)
    {
        $school_id = $request->get('school_id');
        return fetchvidslinks();
    }

    public function fetch_deleted_assessment(Request $request){
        $data = $this->AstNextPupilAssessmentProvider->fetch_deleted_records($request);
        return response($data);
    }

    public function get_packages(Request $request){
        $data = $this->AstNextPupilAssessmentProvider->get_packages($request);
        return $data;
    }

    public function get_ast_resources(){
        return $this->ResourcesBuilder->astnextresources();
    }

    public function get_admin_resources(){
        return $this->ResourcesBuilder->astnextadminresources();
    }

    public function get_booklet_resources(){
        return $this->ResourcesBuilder->astnextbookletresources();
    }
}
