<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\DataSharing\SearchStudentController;
use App\Http\Controllers\DataSharing\ShareStudentController;

Route::get('test_request', 'AstNext\AstNextController@test_request');

//Login
Route::post('fetch-schools','AstNext\AstNextLoginController@getSchools');
Route::get('check-step1', 'AstNext\AstNextLoginController@beforeLoginStep1');
Route::post('check-step2', 'AstNext\AstNextLoginController@beforeLoginStep2');
Route::get('new-login-view', 'AstNext\AstNextLoginController@pupilLoginView');
Route::post('login-view-form', 'AstNext\AstNextLoginController@loginViewForm');
Route::post('pupil-login', 'AstNext\AstNextLoginController@pupilLogin');
Route::post('staff-login', 'AstNext\AstNextLoginController@staffLogin');
Route::post('forgot-password', 'AstNext\AstNextLoginController@forgotPassword');
Route::post('forgot-password-step2', 'AstNext\AstNextLoginController@forgotPasswordStep2');

Route::get('login-email', 'AstNext\AstNextController@TestLoginDetail');
Route::post('send-student-login-details', 'AstNext\AstNextUserSettingController@StudentLoginDetails');
//Route::post('factor-auth/verify-code-and-login', 'AstNext\AstNextFactorAuthenticationController@verifyfactorCodeAndLogIn');
Route::post('factor-auth/resend-token', 'AstNext\AstNextFactorAuthenticationController@resendToken');
Route::post('factor-auth/login-with-token', 'AstNext\AstNextFactorAuthenticationController@signInViaTwoFA');
Route::post('factor-auth/verify-token', 'AstNext\AstNextFactorAuthenticationController@verifyfactorCode');

Route::group(['middleware' => ['connection', 'auth:authorizedusers', 'apiResponseType']], function () {
    Route::get('get-consultant-url', 'AstNext\AstNextController@getConsultantURL');
    Route::post('complete-onboarding-step/{id}', 'AstNext\AstNextController@onboard');
    Route::patch('update-onboard-status', 'AstNext\AstNextController@update_onboard_status');
    Route::post('logout', 'AstNext\AstNextController@logout');
    Route::get('summary', 'AstNext\AstNextAssessmentSummaryController@summary');
    Route::get('schoolyear_specific_summary/{year}/{school_id}', 'AstNext\AstNextAssessmentSummaryController@schoolyear_specific_summary');
    Route::get('fix_priority/{year}/{school_id}', 'AstNext\AstNextAssessmentSummaryController@fix_priority');
    Route::get('check_priority/{year}/{school_id}', 'AstNext\AstNextAssessmentSummaryController@check_priority');
    Route::patch('toggle-take-the-wheel', 'AstNext\AstNextController@toggleTakeTheWheel');
    Route::get('school-package', 'AstNext\AstNextController@schoolPackage');
    Route::get('log', 'AstNext\AstNextController@log');
    Route::get('user-session', 'AstNext\AstNextController@sessionData');
    Route::group(['middleware' => ['school_conn']], function () {
        Route::group(['middleware' => ['otherLanguage']], function () {
            //Author Kevin - Group dash
            Route::get('group-assessment-tracker', 'AstNext\AstNextGroupdashboardController@GroupAssessmenttracker');
            Route::get('event-tracker', 'AstNext\AstNextGroupdashboardController@eventtracker');
//            Route::get('school-tracker-overview', 'AstNext\AstNextGroupdashboardController@schooltrackerdata');
            Route::get('school-tracker-dropdown', 'AstNext\AstNextGroupdashboardController@schooltracker');
            Route::get('implementation-phases/{stage}', 'AstNext\AstNextGroupdashboardController@getsliderdetails');
            Route::get('skills-list', 'AstNext\AstNextGroupdashboardController@getSkills');
            Route::post('skills-insert', 'AstNext\AstNextGroupdashboardController@insertSkills');
            Route::patch('skills-update-notes', 'AstNext\AstNextGroupdashboardController@updateSkillsnotes');
            Route::delete('skills-delete-notes', 'AstNext\AstNextGroupdashboardController@deleteSkillsnotes');
            Route::delete('skills-delete', 'AstNext\AstNextGroupdashboardController@deleteSkills');
            Route::get('analytics/key-implementation-metric', 'AstNext\AstNextGroupdashboardController@data_analytics_ap');
            Route::get('analytics/key-success-metric', 'AstNext\AstNextGroupdashboardController@data_analytics_ass_improvement');
            Route::get('groupdash-filters', 'AstNext\AstNextGroupdashboardController@filters');

            Route::prefix('implementation-schools-data')->group(function () {
                Route::get('{stage}', 'AstNext\AstNextGroupdashboardController@getOverviewSchool');
                Route::get('{stage}/{id}', 'AstNext\AstNextGroupdashboardController@schooltrackerdata');
                Route::patch('{stage}/{id}/update-phase', 'AstNext\AstNextGroupdashboardController@updatePhase');
            });

            /*
            * --------------------------------------------------
            * Group Dashboard Analytics APIs
            * --------------------------------------------------
            * */
            Route::prefix('analytics')->group(function () {
                Route::get('key-performance-metric', 'AstNext\AstNextGroupDashboardAnalyticController@emotionWellBeinganalytic');
                Route::get('key-wellbeing-metric', 'AstNext\AstNextGroupDashboardAnalyticController@studentRisk');
                Route::get('common-risk', 'AstNext\AstNextGroupDashboardAnalyticController@commonRisk');
            });
        });
        Route::group(['middleware' => ['filterChecker']], function () {
            // @todo Group the like-routes accordingly
            //Author Johnnie
            Route::get('/update-db', 'AstNext\AstNextController@updateDB');
            Route::get('/cohort-filters', 'AstNext\AstNextController@api_filters');
            Route::post('/send-login-detail-mail', 'AstNext\AstNextController@send_login_detail_mail');
            Route::get('/monitor-comments/{id}', 'AstNext\AstNextMonitorCommentController@monitor_comment')->where('id', '^\d+$');
            Route::delete('/monitor-comments/{id}', 'AstNext\AstNextMonitorCommentController@delete_monitor_comment')->where('id', '^\d+$');
            Route::get('/monitor-comments', 'AstNext\AstNextMonitorCommentController@getMonitorComments');
            Route::get('/monitor-comments-history', 'AstNext\AstNextMonitorCommentController@getHistoricalMonitorComments');
            //Route::get('/cohort-data', 'AstNext\CohortDataSortController@cohort_data_API');
            Route::get('/cohort-data', 'AstNext\AstNextController@ragPageData');
            Route::get('/cohort-data-v2/{id}', 'AstNext\AstNextRagController@ragDetailsv2');
            Route::get('/cohort-data-notification-v2/{id}', 'AstNext\AstNextRagController@ragNotificationv2');
            Route::get('/cohort-data-v2', 'AstNext\AstNextRagController@ragPageDatav2');
            Route::get('/cohort-student-ap/{id}', 'AstNext\AstNextRagController@ragPageStudentAP');
            Route::post('/monitor-comments', 'AstNext\AstNextMonitorCommentController@create_monitor_comment');

            Route::post('/save-default-filters', 'AstNext\AstNextController@save_default_filters');
            Route::get('/get-default-filters', 'AstNext\AstNextController@get_default_filters');
            Route::get('/contract', 'AstNext\AstNextController@contract');
            Route::get('/ragpage', 'AstNext\AstNextController@ragPageData');

            //Author Omotayo
            Route::post('update-student-email', 'AstNext\AstNextUserSettingController@updateStudentEmail');
            Route::get('get-student-with-no-email', 'AstNext\AstNextUserSettingController@getStudentWithNoEmail');


            Route::get('student-action-plans', 'AstNext\AstNextActionController@studentActionPlans');
            Route::get('family-signposts', 'AstNext\AstNextActionController@studentFamilySignPostActionPlans');
            Route::get('student-action-plans-history', 'AstNext\AstNextActionController@historicalstudentActionPlans');
            Route::get('family-signposts-history', 'AstNext\AstNextActionController@historicalstudentFamilySignPostActionPlans');
            Route::get('/pupil-cohort-data', 'AstNext\AstNextCohortController@pupilCohortData');
            Route::get('risks', 'AstNext\AstNextRiskController@risklist');
            Route::post('create-student-action-plan', 'AstNext\AstNextActionController@createstudentactionplan');
            Route::post('create-family-signpost', 'AstNext\AstNextActionController@createfamilysignpost');
            Route::delete('student-action-plans/{id}', 'AstNext\AstNextActionController@deleteStudentActionplan');
            Route::delete('family-signposts/{id}', 'AstNext\AstNextActionController@deleteFamilySignpost');

            //Author Kevin
            Route::get('/cohort-action-plans', 'AstNext\AstNextActionController@currentcohortactionplan');
            Route::get('/cohort-action-plans-history', 'AstNext\AstNextActionController@historiccohortactionplan');
            Route::get('/group-action-plans', 'AstNext\AstNextActionController@currentgroupactionplan');
            Route::get('/group-action-plans-history', 'AstNext\AstNextActionController@historicgroupactionplan');
            Route::post('group-action-plans', 'AstNext\AstNextActionController@creategroupactionplan');
            Route::post('cohort-action-plans', 'AstNext\AstNextActionController@createcohortactionplan');
            Route::delete('group-action-plans/{id}', 'AstNext\AstNextActionController@deleteGroupActionplan');
            Route::delete('cohort-action-plans/{id}', 'AstNext\AstNextActionController@deleteCohortActionplan');
            Route::get('/training-course', 'AstNext\AstNextPupilAssessmentController@getTrainingCourse');
            Route::get('/training-videos', 'AstNext\AstNextPupilAssessmentController@getTrainingvidsandtexts');

            Route::prefix(getAssTopLevelRoute())->group(function() {
                Route::post('manipulation-response', 'AstNext\AstNextuiuxAssessmentController@get_manipulation_response');
            });
            Route::patch('pseudonyimize-name', 'AstNext\AstNextController@toggleswitch');

            //Author Omotayo
            Route::post('student-action-plans', 'AstNext\AstNextActionController@createstudentactionplan');
            Route::post('family-signposts', 'AstNext\AstNextActionController@createfamilysignpost');

            //Author Omotayo
            Route::get('safeguarding-v2', 'AstNext\AstNextSafeguardingController@safeguarding');
	    

            // Author Johnnie, Omotayo and Kevin
            Route::get('tracking-report', 'AstNext\AstNextCohortReportController@report');
            Route::get('action-plan-report', 'AstNext\AstNextActionPlanReportController@report');

            //Author Omotayo
            Route::get('school-impact-overview/{type}', 'AstNext\AstNextSchoolImpactController@overview');

             /*
                * --------------------------------------------------
                * User Account Setting
                * --------------------------------------------------
                * */
                Route::post('user-account-settings/validate-password', 'AstNext\AstNextUserSettingController@verifyCurrentPassword');
                Route::patch('user-account-settings/update-password', 'AstNext\AstNextUserSettingController@createNewPassword');

             /*
                * --------------------------------------------------
                * Factor Authentication
                * --------------------------------------------------
                * */
                Route::get('factor-auth/school-factor-auth', 'AstNext\AstNextFactorAuthenticationController@getSchoolFactorAuth');
                Route::post('factor-auth/school-factor-auth', 'AstNext\AstNextFactorAuthenticationController@schoolFactorAuth');
                Route::get('factor-auth/user-factor-auth', 'AstNext\AstNextFactorAuthenticationController@getUserFactorAuth');
                Route::post('factor-auth/user-factor-auth', 'AstNext\AstNextFactorAuthenticationController@userFactorAuth');
                

                Route::get('ESR/key-wellbeing-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keyWellBeingMetrics');
                Route::get('ESR/key-implementation-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keyImplementationMetrics');
                Route::get('ESR/key-success-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keySuccessMetrics');
                Route::get('ESR/common-risk', 'AstNext\AstNextExecutiveSummaryReportController@commonRisk');




            Route::group(['middleware' => ['otherLanguage']], function () {
                /*
                * --------------------------------------------------
                * Executive Summary Report
                * --------------------------------------------------
                * */
                Route::prefix('ESR')->group(function () {
                    Route::get('/key-performance-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keyPerformanceMetrics');
                    Route::get('/key-wellbeing-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keyWellBeingMetrics');
                    Route::get('/key-implementation-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keyImplementationMetrics');
                    Route::get('/key-success-metrics', 'AstNext\AstNextExecutiveSummaryReportController@keySuccessMetrics');
                    Route::get('/common-risk', 'AstNext\AstNextExecutiveSummaryReportController@commonRisk');
                });

                /*
                * --------------------------------------------------
                * School Impact APIs
                * --------------------------------------------------
                * */
                Route::prefix('school-impact')->group(function () {
                    Route::prefix('factor-biases/{bias}')->group(function () {
                        Route::get('/', 'AstNext\AstNextSchoolImpactController@factor_biases');
                        Route::get('factor-mean/{type}', 'AstNext\AstNextSchoolImpactController@factorMeanbyParam');
                        Route::get('adolescent-trends', 'AstNext\AstNextSchoolImpactController@getAdolescentTrends');
                    });

                    Route::prefix('composite-bias')->group(function () {
                        Route::get('/{type}', 'AstNext\AstNextSchoolImpactController@compositeBias');
                        Route::prefix('/{bias}')->group(function () {
                            Route::get('/composite-mean/{type}', 'AstNext\AstNextSchoolImpactController@compositeMeanbyParam');
                            Route::get('/adolescent-trends', 'AstNext\AstNextSchoolImpactController@getCompositeAdolescentTrends');
                        });

                    });

                });

                /*
                * --------------------------------------------------
                * Assessment Tracker APIs
                * --------------------------------------------------
                * */
                Route::prefix('assessment-tracker')->group(function () {
                    Route::get('summary', 'AstNext\AstNextAssessmentController@AssessmentTrackerSummary');
                    Route::get('deleted-records', 'AstNext\AstNextPupilAssessmentController@fetch_deleted_assessment');
                    Route::patch('start-round', 'AstNext\AstNextAssessmentController@startRound');
                    Route::get('export-students', 'AstNext\AstNextPupilAssessmentController@export_students');
                    Route::get('/{type}', 'AstNext\AstNextAssessmentController@AssessmentTracker');
                });

                /*
                * --------------------------------------------------
                * Student APIs
                * --------------------------------------------------
                * */

                Route::prefix('students')->group(function () {
                    Route::get('/', 'AstNext\AstNextController@listStudents');
                    Route::get('risks-for-students', 'AstNext\AstNextRiskController@getgroupStudentRisks');
                    Route::get('/all', 'AstNext\AstNextStudentListController@allStudentList');

                    Route::group([
                        'prefix' => '{id}',
                        'where' => ['id' => '^\d+$']
                    ], function () {
                        Route::get('/', 'AstNext\AstNextPupilAssessmentController@basicInformation');
                        Route::get('goals', 'AstNext\AstNextController@action_plan_goals');
                        Route::get('reasons', 'AstNext\AstNextController@reasons');
                        Route::get('risks', 'AstNext\AstNextRiskController@StudentRisks');
                        Route::get('future-risks', 'AstNext\AstNextRiskController@getRiskSection');
                        Route::get('filtered-action-plan-risks', 'AstNext\AstNextRiskController@getFilteredActionPlanRiskSection');
                        //Route::get('reasons', 'AstNext\AstNextActionController@getCauses');
                    });
                });

                //Author Omotayo
                Route::get('signpost', 'AstNext\AstNextController@signpost');
                Route::get('student-action-plans/{id}', 'AstNext\AstNextActionController@studentActionPlanDetail');
                Route::get('/pupil-risk-cohort-data', 'AstNext\AstNextRiskController@pupilRiskDescriptionData');
                Route::get('/pupil-assessment-responses', 'AstNext\AstNextAssessmentController@pupilAssessmentInformationData');
                Route::get('family-signposts/{id}', 'AstNext\AstNextActionController@familySignPostDetail');
                Route::patch('student-action-plans/{id}/review', 'AstNext\AstNextActionController@updateStudentActionplan');
                Route::patch('family-signposts/{id}/review', 'AstNext\AstNextActionController@updateFamilySignpost');
                Route::get('bulk-send-login-detail-mail', 'AstNext\AstNextAssessmentController@sendbulkmail');

                //Author Kevin
                Route::get('group-action-plans/{id}', 'AstNext\AstNextActionController@studentGroupActionPlanDetail');
                Route::get('cohort-action-plans/{id}', 'AstNext\AstNextActionController@studentcohortActionPlanDetail');
                Route::prefix(getAssTopLevelRoute())->group(function() {
                    Route::get('/assessment-meta', 'AstNext\AstNextuiuxAssessmentController@getAssessmentAudioAndIntro');
                });
                Route::get('/footprint', 'AstNext\AstNextFootprintController@myfootprint');
                Route::get('/my-footprint', 'AstNext\AstNextFootprintController@footprintstage1');
                Route::get('/my-footprint-supporting-resources', 'AstNext\AstNextFootprintController@footprintstage1Resources');
                Route::get('/my-space', 'AstNext\AstNextFootprintController@footprintstage2');
                Route::get('/my-space-supporting-resources', 'AstNext\AstNextFootprintController@footprintstage2Resources');
                Route::patch('group-action-plans/{id}/review', 'AstNext\AstNextActionController@updateGroupActionplan');
                Route::patch('cohort-action-plans/{id}/review', 'AstNext\AstNextActionController@updateCohortActionplan');
                Route::patch('/training-tracker', 'AstNext\AstNextTrainingmatrixController@update_training_matrix');
                Route::get('/training-tracker', 'AstNext\AstNextTrainingmatrixController@list_training_matrix');
                Route::post('/training-tracker/checkall', 'AstNext\AstNextTrainingmatrixController@checkall');
                Route::get('/training-tracker/staffs', 'AstNext\AstNextTrainingmatrixController@listing_training');
                Route::get('/fetchaccrediationrecords', 'AstNext\AstNextAccreditationController@fetchaccrediationrecords');
                Route::patch('updateaccrediationrecords', 'AstNext\AstNextAccreditationController@updateaccrediationrecords');
                Route::get('/getpackages', 'AstNext\AstNextPupilAssessmentController@get_packages');
                Route::get('/get-folder-resources', 'AstNext\AstNextPupilAssessmentController@get_ast_resources');
                Route::get('/get-admin-folder-resources', 'AstNext\AstNextPupilAssessmentController@get_admin_resources');
                Route::get('/get-booklet-resources', 'AstNext\AstNextPupilAssessmentController@get_booklet_resources');
                Route::get('group-goals', 'AstNext\AstNextController@group_goals');
                Route::get('cohort-goals', 'AstNext\AstNextController@cohort_goals');
                Route::post('upload-accreditation-attachments', 'AstNext\AstNextAccreditationController@upload');
                Route::post('upload-accreditation-comments', 'AstNext\AstNextAccreditationController@uploadcomment');
                Route::delete('accreditation-attachments-delete', 'AstNext\AstNextAccreditationController@deleteattachment');

                //Author Johnnie
                Route::get('year-lists', 'AstNext\AstNextPupilAssessmentController@yearList');
                Route::get('year-grouping-lists', 'AstNext\AstNextPupilAssessmentController@yearGroupingList');
                Route::get('assessment-statements-and-audio', 'AstNext\AstNextPupilAssessmentController@getAssessmentAudioAndIntro');
                Route::get('pupil-year-group/{year}', 'AstNext\AstNextPupilAssessmentController@getPupilOnYearGroup');
                Route::delete('assessments/{id}', 'AstNext\AstNextPupilAssessmentController@delete_assessment');
                Route::get('curriculum', 'AstNext\AstNextPsheController@index');

                //Statistic Report
                Route::patch('update-statistic-report/{school_id}/{ass_main_id}', 'AstNext\AstNextStatisticReportController@report');

                //Export CSV Data - Author Megha
                Route::post('fetch-export-records', 'AstNext\AstNextExportCSVDataController@fetchExportRecords');
                Route::post('export-csv', 'AstNext\AstNextExportCSVDataController@exportCsv');

                //Permission records - Author Megha
                Route::get('permission-data', 'AstNext\AstNextPermissionController@permissionData');
                Route::post('fetch-permission-records', 'AstNext\AstNextPermissionController@fetchPermissionRecords');
                Route::post('save-permission', 'AstNext\AstNextPermissionController@saveNewPermission');
                Route::post('export-permission', 'AstNext\AstNextPermissionController@exportPermission');

                //Executive summary report - Authior Megha
                Route::get('executive-reports', 'AstNext\AstNextExecutiveReportController@index');
                Route::post('round-executive-summary-report-data', 'AstNext\AstNextExecutiveReportController@getRoundExecutiveSummaryData');
                Route::post('executive-summary-report-data', 'AstNext\AstNextExecutiveReportController@getExecutiveSummaryData');

                //Import Student data - Author Megha
                Route::post('select-option','AstNext\AstNextImportPupilController@selectOption');
                Route::post('student-export-csv','AstNext\AstNextImportPupilController@allPupilExportCsv');
                Route::post('custom-export-csv','AstNext\AstNextImportPupilController@exportCsv');
                Route::post('upload-pupil-csv','AstNext\AstNextImportPupilController@uploadCSV');
                Route::get('get-sample-data/{option}','AstNext\AstNextImportPupilController@getSampleData');
                Route::post('check-date-formate','AstNext\AstNextImportPupilController@checkDateFormate');
                Route::get('field-matching/{option}/{username_option}','AstNext\AstNextImportPupilController@fieldMatching');
                Route::post('set-new-match-data','AstNext\AstNextImportPupilController@setNewMatchData');
                Route::get('check-temp-data/{option}','AstNext\AstNextImportPupilController@checkTempData');
                Route::post('edit-checking-pupil','AstNext\AstNextImportPupilController@editCheckingPupilData');
                Route::post('edit-changes-value','AstNext\AstNextImportPupilController@editChangesValue');
                Route::post('edit-changes-delete-row','AstNext\AstNextImportPupilController@editDelRow');
                Route::get('name-match/{option}','AstNext\AstNextImportPupilController@nameMatch');
                Route::get('display-name-match/{option}/{username_option}','AstNext\AstNextImportPupilController@displayNameMatching');
                Route::get('save-data/{option}','AstNext\AstNextImportPupilController@saveData');
                Route::get('school-profile/{option}/{username_option}','AstNext\AstNextImportPupilController@schoolProfile');
                Route::post('update-new-mapped', 'AstNext\AstNextImportPupilController@setMappCustomData');
                Route::get('additional-information/{option}/{username_option}', 'AstNext\AstNextImportPupilController@additionalInformation');
                Route::get('custom-fields/{option}/{username_option}', 'AstNext\AstNextImportPupilController@customFields');
                Route::post('final-save-data-pupil', 'AstNext\AstNextImportPupilController@finalSaveData');

                //Import Staff data - Author Megha
                Route::get('initial-check/{option}', 'AstNext\AstNextImportStaffController@intialCheck');
                Route::post('upload-staff-csv', 'AstNext\AstNextImportStaffController@uploadCsv');
                Route::get('staff-profile/{option}', 'AstNext\AstNextImportStaffController@staffProfile');
                Route::post('staff-new-matching', 'AstNext\AstNextImportStaffController@staffNewMatch');
                Route::get('staff-check-temp/{option}', 'AstNext\AstNextImportStaffController@staffCheckTemp');
                Route::get('edit-check-staff-name/{option}', 'AstNext\AstNextImportStaffController@editCheckStaffName');
                Route::post('staff-edit-changes-data', 'AstNext\AstNextImportStaffController@staffEditChangesData');
                Route::post('staff-csv-del-row', 'AstNext\AstNextImportStaffController@staffCSVDeleteRow');
                Route::get('staff-name-matching/{option}', 'AstNext\AstNextImportStaffController@staffNameMatching');
                Route::post('match-update-data', 'AstNext\AstNextImportStaffController@matchUpdateData');
                Route::get('display-name-matching/{option}', 'AstNext\AstNextImportStaffController@staffDisplayNameMatching');
                Route::post('final-save-data', 'AstNext\AstNextImportStaffController@staffFinalSaveData');

                //Edit Pupil Data - Author Megha
                Route::get('fetch-pupil-data/{level}', 'AstNext\AstNextEditPupilDataController@fetchPupilRecords');
                Route::patch('add-pupil', 'AstNext\AstNextEditPupilDataController@addPupil');
                Route::get('edit-pupil-view/{id}/{level}', 'AstNext\AstNextEditPupilDataController@editPupilView');
                Route::post('sponsor-school', 'AstNext\AstNextEditPupilDataController@getSponsoredPupil');
                Route::patch('edit-pupil', 'AstNext\AstNextEditPupilDataController@editPupil');
                Route::delete('delete-pupil', 'AstNext\AstNextEditPupilDataController@deletePupil');
                Route::get('export-pupil-csv/{level}', 'AstNext\AstNextEditPupilDataController@exportPupildataCsv');
                Route::get('get-subschools-data', 'AstNext\AstNextEditPupilDataController@getSubSchools');

                //Edit staff data - Author Palak
                Route::post('fetch-staff-data', 'AstNext\AstNextEditStaffDataController@fetchStaffdata');
                Route::post('add-staff', 'AstNext\AstNextEditStaffDataController@addStaffdata');
                Route::get('get-edit-staff-view/{id}', 'AstNext\AstNextEditStaffDataController@getEditStaffView');
                Route::patch('edit-staff', 'AstNext\AstNextEditStaffDataController@editStaff');
                Route::delete('delete-staff', 'AstNext\AstNextEditStaffDataController@deleteStaff');
                Route::post('export-staff-data', 'AstNext\AstNextEditStaffDataController@exportStaffData');
                Route::get('lead-sp-view', 'AstNext\AstNextEditStaffDataController@leadSpView');
                Route::patch('update-lead-sp-status', 'AstNext\AstNextEditStaffDataController@updatedLeadSpStatus');
                Route::patch('update-campus-lead-sp', 'AstNext\AstNextEditStaffDataController@updateCampusLeadSp');
                           //Wonde Import Student - Author Palak
                Route::post('wonde-student-import', 'AstNext\AstNextImportPupilController@wondeStudentImport');
                Route::post('wonde-student-form-data', 'AstNext\AstNextImportPupilController@wondeStudentFormData');
                Route::post('check-wonde-misid', 'AstNext\AstNextImportPupilController@checkWondeMisid');
                Route::post('student-wonde-compare-data', 'AstNext\AstNextImportPupilController@studentWondeCompareData');
                Route::post('wonde-student-import-data', 'AstNext\AstNextImportPupilController@wondeStudentImportData');
                Route::post('check-multi-wonde-id', 'AstNext\AstNextImportPupilController@getMultiWondeId');
                Route::post('wonde-duplicate-username', 'AstNext\AstNextImportPupilController@wondeDuplicateUsername');
                Route::post('get-null-student-email-address', 'AstNext\AstNextImportPupilController@getNullStudentEmailAddress');
                Route::post('send-wonde-student-import-mail', 'AstNext\AstNextImportPupilController@sendWondeStudentImportMail');

                //Wonde Import Staff - Author Palak
                Route::post('wonde-staff-import-with-checkpop', 'AstNext\AstNextImportStaffController@wondeStaffImport');
                Route::post('show-wonde-staff-compare-data', 'AstNext\AstNextImportStaffController@showWondeStaffCompareData');
                Route::post('select-wonde-staff-data', 'AstNext\AstNextImportStaffController@selectWondeStaffData');
                Route::post('wonde-staff-select-process', 'AstNext\AstNextImportStaffController@wondeStaffSelectProcess');
                Route::post('wonde-import-staff-data', 'AstNext\AstNextImportStaffController@wondeStaffImportData');
                Route::post('check-wonde-duplicate-staff-username', 'AstNext\AstNextImportStaffController@checkWondeDuplicateStaffUsername');
                Route::post('wonde-staff-import-process', 'AstNext\AstNextImportStaffController@wondeStaffImportProcess');

                // TTW . Author Adedeji
                Route::prefix('ttw-report')->group(function () {
                    Route::get('/', 'AstNext\AstNextTTWController@getReportSummary');
                    Route::post('/', 'AstNext\AstNextTTWController@savePupilResponse');
                    Route::delete('/', 'AstNext\AstNextTTWController@resetResponse');
                    Route::get('/chart', 'AstNext\AstNextTTWController@getPupilScoreChartByType');
                    Route::post('/chart', 'AstNext\AstNextTTWController@setChartImage');
                    Route::post('/generate', 'AstNext\AstNextTTWController@generateReport');
                });

                Route::prefix('user-configs')->group(function() {
                    Route::get('/', 'AstNext\AstNextUserConfigsController@get');
                    Route::post('/', 'AstNext\AstNextUserConfigsController@store');
                });
            });
            Route::get('test_filters', 'AstNext\AstNextController@test_filters');
        });
        Route::group(['middleware' => ['otherLanguage'], 'prefix' => getAssTopLevelRoute()], function () {
            Route::post('answer-question', 'AstNext\AstNextuiuxAssessmentController@answer_question');
        });

        Route::prefix('data-sharing')->group(function () {
            Route::get('search-student', [ ShareStudentController::class, 'searchStudent']);
            Route::post('share-student', [ ShareStudentController::class, 'shareStudent']);
            Route::get('get-shared-students', [ ShareStudentController::class, 'getSharedStudent']);
            Route::patch('accept-shared-student', [ ShareStudentController::class, 'acceptStudentList']);
            Route::patch('stop-sharing-student-data', [ ShareStudentController::class, 'stopSharingStudentData']);
            Route::get('shared-students', [ ShareStudentController::class, 'getSharedStudent']);
            Route::patch('toggle-decline-student', [ ShareStudentController::class, 'removeSharedStudent']);
            Route::patch('cancel-syncing-student-data', [ ShareStudentController::class, 'cancelSharedStudent']);
            Route::patch('activate_shared_data/{round}/{school_id}/{academic_year}', 'AstNext\AstNextController@testActivationOfRoundDS');
        });
    });
});

//Statistic Report
Route::group(['middleware' => ['webhook']], function () {
    Route::patch('assessment_summary/{school_id}/{ass_main_id}', 'AstNext\AstNextStatisticReportController@report');
    Route::patch('fix_ass_part_failed_report/{school_id}', 'AstNext\AstNextStatisticReportController@fix_ass_part_failed_report');
    Route::post('action-plan-reminder', 'AstNext\AstNextAPReminderController@reminder');
    Route::post('publish/{school_id}/{ass_main_id}', 'AstNext\AstNextStatisticReportController@publish');
});
