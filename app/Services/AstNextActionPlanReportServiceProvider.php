<?php
namespace App\Services;
use App\Models\Dbschools\Model_rep_group_actionplan;
use App\Models\Dbschools\Model_rep_group_pdf;
use App\Models\Dbschools\Model_population;
use App\Services\CohortServiceProvider;
use App\Services\ActionPlanServiceProvider;
use App\Util\Grouping\Composite\Composite;
use App\Models\Dbschools\Model_arr_year;
use App\Util\Builder\Report\Report;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_report_actionplan;
use App\Models\Dbschools\Model_report_family_signpost;
use App\Util\Grouping\AssessmentImprovement\AssessmentImprovement;
use App\Util\Grouping\TeacherReview\TeacherReview;
use App\Services\CohortDataFilterServiceProvider;
use App\Services\ActionPlanMetaServiceProvider;
use App\Models\Dbschools\Model_statistic_report;
use App\Models\Dbglobal\Model_dat_schools;

class AstNextActionPlanReportServiceProvider
{
    private $improvement;
    private $review;
    private $validActionPlancount;
    private $pos_review;
    public function __construct()
    {
        $this->assImprove = new AssessmentImprovement();
        $this->teacherReview = new TeacherReview();
        $this->filter = new CohortDataFilterServiceProvider();
        $this->actionPlan = new ActionPlanServiceProvider();
        $this->actionPlanMeta = new ActionPlanMetaServiceProvider();
        $this->assMain = new Model_ass_main();
        $this->familySignPost = new Model_report_family_signpost();
        $this->report = new Report();
        $this->studentActionPlan = new Model_report_actionplan();
        $this->arr_year_model = new Model_arr_year();
        $this->rep_group_actionplan_model = new Model_rep_group_actionplan();
        $this->rep_group_pdf = new Model_rep_group_pdf();
        $this->population_model = new Model_population();
        $this->actionPlanServiceProvider = new ActionPlanServiceProvider();
        $this->CohortServiceProvider = new CohortServiceProvider();
        $this->compositeRisk = new Composite();
        $this->datSchools_model = new Model_dat_schools();
        $this->model_statistic_report = new Model_statistic_report();
        $this->improvement = 0;
        $this->review = 0;
        $this->pos_review = 0;
        $this->no_impact_review = 0;
        $this->not_review = 0;
        $this->validActionPlancount = 0;
    }

    public function meta( $request )
    {
        $school_id = $request->get('school_id');
        $filter = $this->filter->AssessmentFilters($request);
        $filter = setDefaultFilter($filter, $school_id);
        //$filter['round'][0] = RoundLatest( $filter['academic_year'][0] );
        $yearList = $this->actionPlanMeta->academicYearsList( $request->get('school_id') );
        return [ 'filter' => $filter, 'yearList' => $yearList, 'school_id' => $school_id ];
    }

    public function actionPlanObject( $actionPlan, $current_assessment_list, $immediate_previous_assessment_list, $assessment_type, $action_plan_summary ) {
        foreach( $actionPlan as  $data) {
            $students = array_column( $action_plan_summary, 'student' );
            $user_ids = array_column( $students, 'id' );
            if( !in_array( $data->created_on, $user_ids ) ) {
                list( 'current_score' => $current_score, 'previous_score' => $previous_score ) = ExtractAssementInfo($data, $current_assessment_list, $immediate_previous_assessment_list, $assessment_type );
                if( $previous_score !== null) {
                    $this->validActionPlancount++;
                    $improvement =  $this->assImprove->StudentAssessmentImprovement( $current_score, $previous_score );
                    if( $improvement )
                        $this->improvement = $this->improvement + 1;
                    $review = Review( $data->review );
                    if( $review )
                        $this->review = $this->review + 1;
                    if( $review == 'Positive Impact' )
                        $this->pos_review = $this->pos_review + 1;
                    else if( $review == 'No Impact yet')
                        $this->no_impact_review = $this->no_impact_review + 1;
                    else if( $review == 'Not Reviewed')
                        $this->not_review = $this->not_review + 1;
                    $action_plan_summary[] = $this->report->action_plan_summary( $data, $current_assessment_list, $immediate_previous_assessment_list, $current_score, $previous_score );
                }
            }
        }
        return $action_plan_summary;
    }

    public function GroupactionPlanObject( $actionPlan, $current_assessment_list, $immediate_previous_assessment_list, $assessment_type, $action_plan_summary ) {
        $studentdetails = [];
        foreach( $actionPlan as  $k=>$data) {
            $curr_grp = $action_plan_summary[$data->id]['group'] ?? [];
            $students = array_column( $curr_grp, 'student' );
            $user_ids = array_column( $students, 'id' );
            if (isset($data['pop_id']) && !empty($data['pop_id'])) {
                $str_arr = explode(",", $data['pop_id']);
                if (isset($str_arr)) {
                    foreach ($str_arr as $k1=>$s) {
                        if( !in_array( $s, $user_ids ) ) {
                            list( 'current_score' => $current_score, 'previous_score' => $previous_score ) = ExtractAssementInfo_group_cohort($data, $current_assessment_list, $immediate_previous_assessment_list, $assessment_type,$s );
                            if( $previous_score !== null) {
                                $this->validActionPlancount++;
                                $improvement =  $this->assImprove->StudentAssessmentImprovement( $current_score, $previous_score );
                                // if( $improvement )
                                //     $this->improvement = $this->improvement + 1;
                                // Group-Review -----START
                                if($data->review == null){
                                    $review = "Not Reviewed";
                                }elseif(isJson($data->review)=='true'){
                                    $review = Group_Review_student( $data->review, $s );
                                }else{
                                    $review = "Not Reviewed";
                                }
                                $r = $this->population_model->get($s);
                                if (isset($r['firstname'])){
                                    $studentdetails = (array)$studentdetails;
                                    $studentdetails['firstname'] = $r['firstname'];
                                    $studentdetails['student_id'] = (int) $s;
                                    $studentdetails['lastname'] = $r['lastname'];
                                    $studentdetails['gender'] = $r['gender'];
                                }
                                $studentdetails = (object) $studentdetails ;
                                $action_plan_summary1 = $this->report->groupaction_plan_summary( $data, $current_assessment_list, $immediate_previous_assessment_list, $current_score, $previous_score,$studentdetails,$review );
                                if($data->id==$action_plan_summary1['id'])
                                    $action_plan_summary[$data->id]['group'][] = $action_plan_summary1;
                                    if( $action_plan_summary1['assessment_improvement'] == true) $this->improvement = $this->improvement + 1;
                                    if( $action_plan_summary1['teacher_review'] == 'Not Reviewed') $this->not_review = $this->not_review + 1;
                                    else if( $action_plan_summary1['teacher_review'] == 'Positive Impact') $this->pos_review = $this->pos_review + 1;
                                    else if( $action_plan_summary1['teacher_review'] == 'No Impact yet' ) $this->no_impact_review = $this->no_impact_review + 1;
                                    $this->review = $this->review + 1;
                            }
                        }
                    }
                }
            }
        }
        return $action_plan_summary;
    }

    public function CohortactionPlanObject( $actionPlan, $current_assessment_list, $immediate_previous_assessment_list, $assessment_type, $action_plan_summary,$year,$round ) {
        foreach( $actionPlan as  $k=>$data) {
            if (isset($data['pop_id']) && !empty($data['pop_id'])) {
                $filter = isset($data['filter']) ? $data['filter'] : '';
                if ($filter!='')
                    $final_filter = $this->actionPlanServiceProvider->Display_title_filters($filter);
                $str_arr = explode(",", $data['pop_id']);
                if (isset($str_arr)) {
                    $present_scores = $old_scores = [];
                    $present_counter = $old_counter = 0;
                    $this->validActionPlancount++;
                    $is_composite = false;
                    parse_str($filter, $custom);
                    if(isset($custom['prev_cache'])){
                        unset($custom['prev_cache']);
                    }if(isset($custom['submit'])){
                        unset($custom['submit']);
                    }if(isset($custom['month'])){
                        unset($custom['month']);
                    }if(isset($custom['filters:accyear'])){
                        $custom['academic_year'][] = $year;
                        unset($custom['filters:accyear']);
                    }if(isset($custom['rtype'])){
                        unset($custom['rtype']);
                    }if(isset($custom['syrs'])){
                        $custom['year_group'] = $custom['syrs'];
                        unset($custom['syrs']);
                    }if(isset($custom['accyear'])){
                        $custom['academic_year'][] = $year;
                        unset($custom['accyear']);
                    }if(isset($custom['assessment_round'])){
                        $custom['round'][] = $round;
                    }if(!isset($custom['assessment_round'])){
                        $custom['round'][] = $round;
                    }
                    unset($custom['assessment_round']);
                    $query =  $this->assMain->Assessment($custom, [ 'sch', 'hs' ]);
                    $present_data = $query->get();
                    $selected_bias = BiasAbreviationToLabel($data->type_banc);
                    $exactname = BiasAbreviationToName($data->type_banc);
                    if($selected_bias=='OVER_REGULATION'){
                        $pupil_ids = $present_data->pluck('pop_id')->toArray();
                        $dat_stat_OR_present = $this->model_statistic_report->GetReportData($pupil_ids,$custom);
                        $dat_stat_OR_ids_present = $dat_stat_OR_present->pluck('pupil_ids')->toArray();
                    }
                    foreach($present_data as $value){
                        if($selected_bias=='SELF_DISCLOSURE'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->P >= 0 && $value->P <= 3)
                                    $present_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->P > 11.25 && $value->P <= 15)
                                    $present_counter++;
                            }
                            $present_scores[] = $value->P;
                        }elseif($selected_bias=='TRUST_OF_SELF'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->S >= 0 && $value->S <= 3)
                                    $present_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->S > 11.25 && $value->S <= 15)
                                    $present_counter++;
                            }
                            $present_scores[] = $value->S;
                        }elseif($selected_bias=='TRUST_OF_OTHERS'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->L >= 0 && $value->L <= 3)
                                    $present_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->L > 11.25 && $value->L <= 15)
                                    $present_counter++;
                            }
                            $present_scores[] = $value->L;
                        }elseif($selected_bias=='SEEKING_CHANGE'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->X >= 0 && $value->X <= 3)
                                    $present_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->X > 11.25 && $value->X <= 15)
                                    $present_counter++;
                            }
                            $present_scores[] = $value->X;
                        }else{
                            $is_composite = true;
                            if (in_array($value->pop_id, $dat_stat_OR_ids_present))
                                $present_counter++;
                        }
                    }
                    // if ($custom['round'][0]==1){
                    //     $custom['academic_year'][] = $custom['academic_year'][0]-1;
                    //     unset($custom['round']);
                    //     $custom['round'][0] = 3;
                    // }elseif ($custom['round'][0]==2){
                    //     $custom['academic_year'][] = $custom['academic_year'];
                    //     unset($custom['round']);
                    //     $custom['round'][0] = 1;
                    // }else{
                    //     $custom['academic_year'][] = $custom['academic_year'];
                    //     unset($custom['round']);
                    //     $custom['round'][0] = 2;
                    // }
                    
                    $custom = getImmediateLastAssessment( $custom );
                    $query =  $this->assMain->Assessment($custom, [ 'sch', 'hs' ]);
                    $old_data = $query->get();
                    if($selected_bias=='OVER_REGULATION'){
                        $pupil_ids_old = $old_data->pluck('pop_id')->toArray();
                        $dat_stat_OR_old = $this->model_statistic_report->GetReportData($pupil_ids_old,$custom);
                        $dat_stat_OR_ids = $dat_stat_OR_old->pluck('pupil_ids')->toArray();
                    }
                    foreach($old_data as $value){
                        if($selected_bias=='SELF_DISCLOSURE'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->P >= 0 && $value->P <= 3)
                                    $old_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->P > 11.25 && $value->P <= 15)
                                    $old_counter++;
                            }
                            $old_scores[] = $value->P;
                        }elseif($selected_bias=='TRUST_OF_SELF'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->S >= 0 && $value->S <= 3)
                                    $old_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->S > 11.25 && $value->S <= 15)
                                    $old_counter++;
                            }
                            $old_scores[] = $value->S;
                        }elseif($selected_bias=='TRUST_OF_OTHERS'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->L >= 0 && $value->L <= 3)
                                    $old_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->L > 11.25 && $value->L <= 15)
                                    $old_counter++;
                            }
                            $old_scores[] = $value->L;
                        }elseif($selected_bias=='SEEKING_CHANGE'){
                            if(strpos($exactname, 'Low') !== false){
                                if ($value->X >= 0 && $value->X <= 3)
                                    $old_counter++;
                            }if(strpos($exactname, 'High') !== false){
                                if ($value->X > 11.25 && $value->X <= 15)
                                    $old_counter++;
                            }
                            $old_scores[] = $value->X;
                        }else{
                            $is_composite = true;
                            if (in_array($value->pop_id, $dat_stat_OR_ids))
                                $old_counter++;
                        }
                    }
                    if(count($old_scores)>0){
                        $previous_percent = round(($old_counter / count($old_scores))*100, 2);
                        $prev_mean_percent =  round(array_sum($old_scores) / count($old_scores), 2);
                    }else{
                        $previous_percent = 0;
                        $prev_mean_percent = 0;
                    }
                    if(count($present_scores)>0){
                        $current_percent = round(($present_counter / count($present_scores))*100, 2);
                        $current_mean_percent =  round(array_sum($present_scores) / count($present_scores), 2);
                    }else{
                        $current_percent = 0;
                        $current_mean_percent = 0;
                    }
                    // $decrease = $previous_percent - $current_percent;
                    // $red_in_bias = $decrease;
                    // if($decrease>0)
                    //     $red_in_bias = round(($decrease / $previous_percent)*100, 2);
                    // else
                    $red_in_bias = 0;
                    $review = Review( $data->review );
                    // if( $review == 'Positive Impact' )
                    //     $this->pos_review = $this->pos_review + 1;
                    // if( $review )
                    //     $this->review = $this->review + 1;
                    // else if( $review == 'No Impact yet')
                    //     $this->no_impact_review = $this->no_impact_review + 1;
                    // else if( $review == 'Not Reviewed')
                    //     $this->not_review = $this->not_review + 1;

                    $improvement =  $this->assImprove->StudentAssessmentImprovement( $current_percent, $previous_percent );
                    
                    if($is_composite==true){
                        $prev_mean_percent = null;
                        $current_mean_percent = null;
                        if($present_counter!=0){
                            $current_percent = round(($present_counter / count($present_data))*100, 2);
                        }else{
                            $current_percent = 0;
                        }if($old_counter!=0){
                            $previous_percent = round(($old_counter / count($old_data))*100, 2);
                        }else{
                            $previous_percent = 0;
                        }
                    }
                    unset($custom,$selected_bias,$is_composite,$exactname);
                    $action_plan_summary[] = $this->report->cohortaction_plan_summary($final_filter,$data,$current_percent,$previous_percent,$current_mean_percent,$prev_mean_percent,$red_in_bias);
                }
            }
        }
        return $action_plan_summary;
    }

    public function process( $request, $type ) {
        $this->improvement = 0;
        $this->review = 0;
        $this->pos_review = 0;
        $this->validActionPlancount = 0;
        list( 'filter' => $filter, 'yearList' => $yearList, 'school_id' => $school_id  ) = $this->meta( $request );
        //Get Current Assessment
        $current_assessment = $this->assMain->GetFirstCompletedAssessment( $filter );
        $current_assessment_list =  $this->assMain->getAssessmentReport($filter, $type, [] );
        $current_student_ids = PluckStudentId($current_assessment_list);
        //Get past assessment round and academic year
        $student_ids = [];
        $immediate_previous_assessment_list = $immediate_previous_assessment = $group_student_ids = null;
        foreach( [1,2,3] as $key => $round ) {
            $filter = buildFetchParamForPastAssessment($filter['round'][0], $filter['academic_year'][0], $school_id);
            if( $filter == null ) {
                break;
            }
            else if( count( $current_student_ids ) > 0 ) {
                //Get next assessment round and academic year
                $immediate_previous_assessment[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $this->assMain->GetFirstCompletedAssessment( $filter );
                $previous_assessment_list =  $this->assMain->getAssessmentReport($filter, $type, $current_student_ids );
                $immediate_previous_assessment_list[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $previous_assessment_list;
                $_ids = PluckStudentId( $immediate_previous_assessment_list[ $filter['round'][0].'-'.$filter['academic_year'][0] ] );

                $group_student_ids[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $_ids;
                $current_student_ids = array_diff( $current_student_ids, $_ids);
                $current_student_ids = array_values($current_student_ids);
                $student_ids = array_merge( $student_ids, $_ids );
            }
        }
        return [ 'current_assessment_list' => $current_assessment_list,
                 'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
                 'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
                 'immediate_previous_assessment' => $immediate_previous_assessment,
                 'group_student_ids' => $group_student_ids
                 ];
    }

    public function cohort_process( $request, $type ) {
        $this->improvement = 0;
        $this->review = 0;
        $this->pos_review = 0;
        $this->validActionPlancount = 0;
        list( 'filter' => $filter, 'yearList' => $yearList, 'school_id' => $school_id  ) = $this->meta( $request );
        //Get Current Assessment
        $current_assessment = $this->assMain->GetFirstCompletedAssessment( $filter );
        $current_assessment_list =  $this->assMain->getAssessmentReport($filter, $type, [] );
        $current_student_ids = PluckStudentId($current_assessment_list);
        //Get past assessment round and academic year
        $student_ids = [];
        $immediate_previous_assessment_list = $group_student_ids = $immediate_previous_assessment = null;
        foreach( [1,2] as $key => $round ) {
            $filter = buildFetchParamForPastAssessment($filter['round'][0], $filter['academic_year'][0], $school_id);
            if( $filter == null ) {
                break;
            }
            else {
                //Get next assessment round and academic year
                $immediate_previous_assessment[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $this->assMain->GetFirstCompletedAssessment( $filter );
                $previous_assessment_list =  $this->assMain->getAssessmentReport($filter, $type, $current_student_ids );
                $immediate_previous_assessment_list[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $previous_assessment_list;
                $_ids = PluckStudentId( $immediate_previous_assessment_list[ $filter['round'][0].'-'.$filter['academic_year'][0] ] );

                $group_student_ids[ $filter['round'][0].'-'.$filter['academic_year'][0] ] = $_ids;
                $current_student_ids = array_diff( $current_student_ids, $_ids);
                $current_student_ids = array_values($current_student_ids);
                $student_ids = array_merge( $student_ids, $_ids );
            }
        }
        return [ 'current_assessment_list' => $current_assessment_list,
                 'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
                 'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
                 'immediate_previous_assessment' => $immediate_previous_assessment,
                 'group_student_ids' => $group_student_ids
                 ];
    }
    public function family_signpost_plan_summary($request)
    {
        $this->review = 0;
        $this->pos_review = 0;
        $this->no_impact_review = 0;
        $this->not_review = 0;
        list( 'current_assessment_list' => $current_assessment_list,
        'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
        'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
        'immediate_previous_assessment' => $immediate_previous_assessment,
        'group_student_ids' => $group_student_ids
         ) = $this->process( $request, [ 'at' ] );

        $studentActionPlan = $action_plan_summary = [];
        if( $group_student_ids ) {
            $arr_list = array_keys($group_student_ids);
            foreach( $arr_list as $key => $list ) {
                $familySignPost[$list] = $this->familySignPost->getFamilySignPostByDate( $current_assessment, $immediate_previous_assessment[$list], $group_student_ids[$list] );
                $action_plan_summary = $this->actionPlanObject( $familySignPost[$list], $current_assessment_list, $immediate_previous_assessment_list[$list], 'OUT_OF_SCHOOL', $action_plan_summary );
            }
        }

        $improvement_percentage = PercentageCalculation( $this->validActionPlancount, $this->improvement );
        $review_percentage = PercentageReviewCalculation( $this->review, $this->pos_review, $this->no_impact_review, $this->not_review );
        return $this->report->action_plan_summary_object( $improvement_percentage, $review_percentage, $action_plan_summary );
    }

    public function student_action_plan_summary($request)
    {
        $this->review = 0;
        $this->pos_review = 0;
        $this->no_impact_review = 0;
        $this->not_review = 0;
        list( 'current_assessment_list' => $current_assessment_list,
              'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
              'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
              'immediate_previous_assessment' => $immediate_previous_assessment,
              'group_student_ids' => $group_student_ids
            ) = $this->process( $request, [ 'sch', 'hs' ] );
        $studentActionPlan = $action_plan_summary = [];
        if( $group_student_ids ) {
            $arr_list = array_keys($group_student_ids);
            foreach( $arr_list as $key => $list ) {
                $studentActionPlan[$list] = $this->studentActionPlan->getActionPlanPostByDate( $current_assessment, $immediate_previous_assessment[$list], $group_student_ids[$list] );
                $action_plan_summary = $this->actionPlanObject( $studentActionPlan[$list], $current_assessment_list, $immediate_previous_assessment_list[$list], 'IN_SCHOOL', $action_plan_summary );
            }
        }


        $improvement_percentage = PercentageCalculation( $this->validActionPlancount, $this->improvement );
        $review_percentage = PercentageReviewCalculation( $this->review, $this->pos_review, $this->no_impact_review, $this->not_review );
        return $this->report->action_plan_summary_object( $improvement_percentage, $review_percentage, $action_plan_summary );
    }

    public function monitor_comment_summary($request){
        $query_filter = FullQueryfilter($request);
        $getPupilDetails = $this->arr_year_model->getPupilWithQueryFilter($query_filter);
    }

    public function group_action_plan_summary($request)
    {
        $this->review = 0;
        $this->pos_review = 0;
        $this->no_impact_review = 0;
        $this->not_review = 0;
        list( 'current_assessment_list' => $current_assessment_list,
        'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
        'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
        'immediate_previous_assessment' => $immediate_previous_assessment,
        'group_student_ids' => $group_student_ids
         )= $this->process( $request, [ 'sch', 'hs' ] );

        $action_plan_summary = $tmp_action_plan_summary = $studentActionPlan = [];
        if( $group_student_ids ) {
            $arr_list = array_keys($group_student_ids);
            foreach( $arr_list as $key => $list ) {
                $studentActionPlan[$list] = $this->rep_group_actionplan_model->getActionPlanPostByDate( $current_assessment, $immediate_previous_assessment[$list], $group_student_ids[$list] );
                $action_plan_summary = $this->GroupactionPlanObject($studentActionPlan[$list], $current_assessment_list, $immediate_previous_assessment_list[$list], 'IN_SCHOOL', $action_plan_summary );
            }
            if(!empty($action_plan_summary)){
                foreach($action_plan_summary as $k=>$v){
                    $tmp_action_plan_summary[]=$v;
                }
            }
        }
        $improvement_percentage = PercentageCalculation( $this->validActionPlancount, $this->improvement );
        $review_percentage = PercentageReviewCalculation( $this->review, $this->pos_review, $this->no_impact_review, $this->not_review );
        return $this->report->action_plan_summary_object( round($improvement_percentage), $review_percentage, $tmp_action_plan_summary );
    }

    public function cohort_action_plan_summary($request){
        $this->review = 0;
        $this->pos_review = 0;
        $this->no_impact_review = 0;
        $this->not_review = 0;
        $this->validActionPlancount = $this->improvement = 0;
        list( 'current_assessment_list' => $current_assessment_list,
        'immediate_previous_assessment_list' => $immediate_previous_assessment_list,
        'student_ids' => $student_ids, 'current_assessment' => $current_assessment,
        'immediate_previous_assessment' => $immediate_previous_assessment,
        'group_student_ids' => $group_student_ids
         )= $this->cohort_process( $request, [ 'sch', 'hs' ] );
         $school_id = $request->get('school_id');
         $current_school_year = IsDataAvailableInYear($school_id);
         $custom_year = $current_school_year;
         $year = $request->has("academic_year") ? $request->get("academic_year") : $custom_year;
         $round = $request->has("assessment_round") ? $request->get("assessment_round") : RoundLatest($year);

         $action_plan_summary = $studentActionPlan = $current_pupil_ids = $capfilter = [];
        if( $group_student_ids ) {
            $arr_list = array_keys($group_student_ids);
            foreach ($current_assessment_list as $assessment_list) {
                $current_pupil_ids[] = $assessment_list['pop_id'];
            }
            foreach( $arr_list as $key => $list ) {
                if( $immediate_previous_assessment[$list] != null ) {
                    $studentActionPlan[$list] = $this->rep_group_pdf->getActionPlanPostByDate( $current_assessment, $immediate_previous_assessment[$list], $current_pupil_ids );
                    $capdata = $this->ExtractStudentIDFromCAP($studentActionPlan[$list], $current_pupil_ids, $capfilter);
                    $studentActionPlan[$list] = $capdata['caplist'];
                    $capfilter = $capdata['capfilter'];
                    if( count($studentActionPlan[$list]) > 0 ) {
                        $action_plan_summary = $this->CohortactionPlanObject($studentActionPlan[$list], $current_assessment_list, $immediate_previous_assessment_list[$list], 'IN_SCHOOL', $action_plan_summary,$year,$round );
                        break;
                    }
                }
            }
            if(count($action_plan_summary)>0)
                $action_plan_summary = array_map("unserialize", array_unique(array_map("serialize", $action_plan_summary)));
        }
        foreach( $action_plan_summary as $summary ) {
            if( $summary['assessment_improvement'] == true) $this->improvement = $this->improvement + 1;
            if( $summary['teacher_review'] == 'Not Reviewed') $this->not_review = $this->not_review + 1;
            else if( $summary['teacher_review'] == 'Positive Impact') $this->pos_review = $this->pos_review + 1;
            else if( $summary['teacher_review'] == 'No Impact yet' ) $this->no_impact_review = $this->no_impact_review + 1;
            $this->review = $this->review + 1;
        }
        $improvement_percentage = PercentageCalculation( count($action_plan_summary), $this->improvement );
       
        $review_percentage = PercentageReviewCalculation( $this->review, $this->pos_review, $this->no_impact_review, $this->not_review );
        return $this->report->action_plan_summary_object( round($improvement_percentage), $review_percentage, $action_plan_summary );
    }

    public function ExtractStudentIDFromCAP($cap, $student_ids, $capfilter) {
        $caplist = [];
        foreach($cap as $ap) {
            $pop_ids = explode(',',$ap->pop_id);
            $intersect = array_intersect($pop_ids,$student_ids);
            $filter = $this->actionPlanServiceProvider->Display_title_filters($ap->filter);
            $exist = in_array($filter, $capfilter );
            if( count($intersect) > 0 && !$exist )  {
                $caplist[] = $ap;
                $capfilter[] = $filter;
            }
        }
        
        return [ 'caplist' => $caplist, 'capfilter' => $capfilter ];
    }
}
