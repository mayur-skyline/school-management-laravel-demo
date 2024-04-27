<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\WondeSSO\WondeSSOController;
/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
Route::group(['middleware' => ['nocache','header']], function () {
Route::get('test-dummy-mail', 'Sga\Sga_controller@DummyMail');


# Wonde SSO
Route::get('wonde/redirect', [WondeSSOController::class, 'WondeRedirectFunction']);
Route::get('wonde/auth', [ WondeSSOController::class, 'RedirectForLogin']);
Route::get('wonde/logout', [ WondeSSOController::class, 'LogOut']);

#--------DEFAULT ROUTES
Route::get('file-not-found', 'Staff\Astracking\Platform_ast_controller_staff@fileNotFound');
Route::post('multi-file-unlink', 'Staff\Astracking\Platform_ast_controller_staff@multiFileUnlink');
//Route::get('/', 'Common\Login_controller@index');       // old login default route   
//Route::get('/', 'Common\New_Login_controller@index');   // new login default route
Route::post('common', 'Common\Login_controller@common');
Route::get('health', 'Common\Login_controller@test');

#------- encryption & decryption & score endpoints controller --------------------
Route::post('encrypt_decrypt', 'Common\Login_controller@encryptDecryptApi');
Route::post('score', 'Common\Login_controller@scoreApi');

# ---------------- User Automated Registration --------------------
Route::get('registration', 'Common\Login_controller@automatedRegistration');
Route::get('new-registration', 'Common\Login_controller@automatedRegistration');
Route::post('registration-update', 'Common\Login_controller@registrationUpdate');
# ---------------- Express Login --------------------
Route::get('persistence-login/{string?}', 'Common\Login_controller@persistenceLoginPage')->middleware('status2');
//Route::get('persistence-login/{string?}', 'Common\Login_controller@persistenceLoginPage');
Route::post('login-persistence-user', 'Common\Login_controller@loggedInPersistenceUser')->middleware('status2');

Route::get('login-view', 'Common\Login_controller@pupilLoginView')->middleware('status3', 'http_referrer');
Route::get('staff-login-view', 'Common\Login_controller@staffLoginView')->middleware('status3', 'http_referrer');

Route::post('pupil-login', 'Common\Login_controller@pupilLogin')->middleware('status3');
Route::post('staff-login', 'Common\Login_controller@staffLogin')->middleware('status3');

Route::get('full-logout', 'Common\Login_controller@fullLogoutFromSystem');
Route::get('sga-logout', 'Common\Login_controller@sgaLogout');
Route::get('logout', 'AstNext\AstNextController@logout');

Route::get('user-platform', 'Common\Login_controller@userPlatform');

Route::get('/check-step1', 'Common\Login_controller@beforeLoginStep1')->middleware('status4');
Route::get('/check-step2', 'Common\Login_controller@beforeLoginStep2')->middleware('status4');
//Route::get('/login', 'Common\Login_controller@beforeLoginStep2');    // old login route   
Route::get('/login', 'Common\New_Login_controller@beforeLoginStep2');    // new login route   
Route::get('/backto-step1', 'Common\Login_controller@backToStep1')->middleware('status3');
Route::get('/redirect-new-login', 'Common\Login_controller@redirectNewLogin');

// separate route for the new login process testing
Route::any('get-school-list', 'Common\New_Login_controller@getSchoolList');
Route::get('login-viewto-check-step2', 'Common\New_Login_controller@beforeLoginStep2');
Route::any('set-form-session', 'Common\New_Login_controller@SetFormSession');
//Route::any('login-view-form', 'Common\New_Login_controller@LoginViewForm')->middleware('status3', 'http_referrer');


//Route::get('new-login-view', 'Common\New_Login_controller@pupilLoginView')->middleware('status3', 'http_referrer');
// Route::get('new-staff-login-view', 'Common\New_Login_controller@staffLoginView')->middleware('status3', 'http_referrer');

Route::post('new-pupil-login', 'Common\New_Login_controller@pupilLogin')->middleware('status3');
Route::post('new-staff-login', 'Common\New_Login_controller@staffLogin')->middleware('status3');

//Route::get('/new-check-step1', 'Common\New_Login_controller@beforeLoginStep1')->middleware('status4');
Route::get('/new-check-step2', 'Common\New_Login_controller@beforeLoginStep2')->middleware('status4');

//Route::match(['get', 'post'], 'new-forgot-password', 'Common\New_Password_controller@forgotPassword')->middleware('status3');
//Route::match(['get', 'post'], 'new-forgot-password-step2/{string}/{lang}', 'Common\New_Password_controller@forgotPasswordStep2');
Route::get('/redire-login-view', 'Common\New_Login_controller@redireLoginView');

//cron file controller
Route::get('national-school', 'Common\Checkers\Cron_controller@nationalSchool');
Route::get('composite-risk', 'Common\Checkers\Cron_controller@compositeRisk');
Route::get('actionplan-impact-ref-consultant', 'Common\Checkers\Cron_controller@actionplanImpactRefConsultant');
Route::get('import-adminusers', 'Common\Checkers\Cron_controller@importAdminusers');
Route::get('acplan-reviewdate-reminder', 'Common\Checkers\Cron_controller@acplanReviewdateReminder');
Route::get('cron-verified-tutor', 'Common\Checkers\Cron_controller@cronVerifiedTutor');
Route::get('transition-email', 'Common\Checkers\Cron_controller@transitionEmail');
Route::get('data-entered', 'Common\Checkers\Cron_controller@dataEntered');
Route::get('ass-incomplete-mail', 'Common\Checkers\Cron_controller@assIncompleteMail');
Route::get('check-reminder-profroma', 'Common\Checkers\Cron_controller@checkReminderProfroma');
Route::get('unsubscribe-proforma', 'Common\Checkers\Cron_controller@unsubscribeProforma');
Route::get('clear-tmp-storage', 'Common\Checkers\Cron_controller@clearTmpStorage');
Route::get('alert-send-email', 'Common\Checkers\Cron_controller@alertSendEmail');
Route::get('automated-global', 'Common\Checkers\Cron_controller@automatedGlobal');
Route::get('automated-tables', 'Common\Checkers\Cron_controller@automatedTables');
Route::get('mean-scores-year-int', 'Common\Checkers\Cron_controller@meanScoresYearInt');
Route::get('phase-reminder', 'Common\Checkers\Cron_controller@phaseRemainder');
Route::get('assistant', 'Common\Checkers\Cron_controller@assistant');
Route::get('otp-view', 'Common\Checkers\Cron_controller@otpView');
Route::post('otp', 'Common\Checkers\Cron_controller@otp');
Route::get('polarbias', 'Common\Checkers\Cron_controller@polarbias');
Route::get('wonde-new-data', 'Common\Checkers\Cron_controller@checkNewWondeData');
Route::get('executive-summary-report', 'Common\Checkers\Cron_controller@getExecutiveSummaryReport');
Route::get('export-data', 'Common\Checkers\Cron_controller@getExportData');

#-------USTEER with encryption Group-----------
Route::get('register-step2', 'App\Usteer\Common_usteer_controller@registerStep2');

#-------End USTEER with encryption Group-----------
Route::get('/logged-in')->middleware('status3@isLoogedIn');

# ---------------- Forgot password ----------------
Route::match(['get', 'post'], 'forgot-password', 'Common\Password_controller@forgotPassword')->middleware('status3');

Route::match(['get', 'post'], 'forgot-password-step2/{string}/{lang}', 'Common\Password_controller@forgotPasswordStep2');

# ---------------- Forgot school code ----------------
Route::match(['get', 'post'], 'forgot-school-code', 'Common\Login_controller@forgotSchoolCode');

Route::get('check-rmunify', 'Common\Login_controller@checkRmUnify');
Route::get('check-wonde', 'Common\Login_controller@checkWondeSso');

Route::get('executive-reports/{id?}', 'Staff\Astracking\Report\Executive_report_controller@index');
Route::get('executive-reports-tile/{id?}', 'Staff\Astracking\Report\Executive_report_controller@index');
Route::post('executive-summary-report-data', 'Staff\Astracking\Report\Executive_report_controller@getExecutiveSummaryData');
Route::post('round-executive-summary-report-data', 'Staff\Astracking\Report\Executive_report_controller@getRoundExecutiveSummaryData');
Route::get('executive-summary-report-previous-data/{id?}', 'Common\Checkers\Script_controller@getNewPreviousDataExecutiveSummary');
Route::get('school-tracking-date-update', 'Common\Checkers\Script_controller@getSchoolTrackingdateChanges');
Route::get('set-assessment-round/{id?}', 'Common\Checkers\Script_controller@setAssessmentRound');
Route::get('set-assessment-round-zero/{id?}', 'Common\Checkers\Script_controller@setAssessmentRoundZero');
});

# ---------------- New react login route ------------------
Route::get('/', 'Common\New_Login_controller@reactDefultLoginPage')->middleware('istokenAvailable'); 
Route::get('/new-check-step1', 'Common\New_Login_controller@reactNewCheckStep1')->middleware('istokenAvailable'); 
Route::get('new-login-view', 'Common\New_Login_controller@reactNewLoginView')->middleware('istokenAvailable'); 

Route::get('new-staff-login-view', 'Common\New_Login_controller@reactNewLoginView')->middleware('istokenAvailable'); 

Route::any('login-view-form', 'Common\New_Login_controller@reactLoginViewForm')->name('login')->middleware('istokenAvailable');
Route::match(['get', 'post'], 'new-forgot-password', 'Common\New_Password_controller@reactForgotPassword');
Route::match(['get', 'post'], 'new-forgot-password-step2/{string}/{lang}', 'Common\New_Password_controller@reactForgotPasswordStep2');

Route::get('/auth-redirect/{en_user_id}', 'AstNext\AstNextController@setSession'); 
Route::get('/astnext-logout', 'AstNext\AstNextController@forgetSession'); 
#React App
Route::get('/ast-next/{react_capture?}/', 'AstNext\AstNextController@app')
->where('react_capture', '[\/\w\.-]*')->middleware('manageSession');
#------------------------#
#L1-pupil
#L2-none
#L3-it-staff
#L4-teacher
#L5-senior-practitioner
#L6-consultant
#L7-admin
#------------------------#
Route::group(['middleware' => ['authLogin', 'otherLanguage', 'nocache', 'header']], function () {

    #-------Common Routes Group-----------
    Route::get('staff-platform', 'Common\Login_controller@staffPlatform');
    Route::get('partial-logout', 'Common\Login_controller@partialLogoutFromSystem');
    Route::get('pupil-platform', 'Common\Login_controller@pupilPlatform');
    Route::group([
        'prefix' => '{package}',
        'where' => ['package' => '(astracking)'],
        'middleware' => 'package'
            ], function() {
#-------pupil Routes Group-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(pupil)'],
            'middleware' => 'level'
                ], function() {
            Route::get('pupil-platform-ast', 'Pupil\Astracking\Platform_ast_controller@platformAst');
            Route::post('validate-session-code', 'Pupil\Astracking\Platform_ast_controller@validateSessionCode');
            Route::post('check-ass-status', 'Pupil\Astracking\Platform_ast_controller@checkAssStatus');
            Route::post('select-audio', 'Pupil\Astracking\Platform_ast_controller@addEditAudioType');
            Route::get('get-assessment', 'Pupil\Astracking\Platform_ast_controller@getPupilAssessment');
            Route::any('save-assessment-rawdata', 'Pupil\Astracking\Platform_ast_controller@saveAssessmentRawdata');
            Route::post('assessment-manipulation', 'Pupil\Astracking\Platform_ast_controller@assessmentManipulation');
            Route::post('get-assessment-manipulation-video', 'Pupil\Astracking\Platform_ast_controller@assessmentManipulationVideo');
            Route::get('student-report', 'Pupil\Astracking\Platform_ast_controller@student_report');
            Route::post('student-answer-save', 'Pupil\Astracking\Platform_ast_controller@student_answer_save');
            Route::post('pupil_score_chart', 'Pupil\Astracking\Platform_ast_controller@pupil_score_chart');
            Route::post('pupil-chart-image', 'Pupil\Astracking\Platform_ast_controller@pupil_chart_image');
            Route::post('pupil-data-delete', 'Pupil\Astracking\Platform_ast_controller@pupil_data_delete');
            Route::post('pupil-start_end-report', 'Pupil\Astracking\Platform_ast_controller@pupil_startEnd_report');
            Route::get('download-pdf-feedback/{pdf_name}', 'Pupil\Astracking\Platform_ast_controller@downloadPdfFeedback');
            Route::post('opt-out-delete', 'Pupil\Astracking\Platform_ast_controller@opt_out_delete');
            Route::post('close-status-data', 'Pupil\Astracking\Platform_ast_controller@closeStatusLastdata');
        });
# ---------------- As Tracking section route ------------------
#-------L-3 L-4 and L-5 Only-----------

        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner)'],
            'middleware' => 'level'
                ], function() {


#----------------------Individual video on/off switch--------------------------
            Route::get('video-on-off', 'Staff\Astracking\Platform_ast_controller_staff@videoOnOff');
            Route::post('check-video-status', 'Staff\Astracking\Platform_ast_controller_staff@checkVideoStatus');
            Route::post('update-video-status', 'Staff\Astracking\Platform_ast_controller_staff@updateVideoStatus');

#----------------------Staff Activity--------------------------
            Route::get('staff-activity', 'Staff\Astracking\Platform_ast_controller_staff@staffActivity');
            Route::post('staff-activity-ajax', 'Staff\Astracking\Platform_ast_controller_staff@staffActivityAjax');

#----------------------Create Shortcut--------------------------
            Route::get('desktop-shortcut', 'Staff\Astracking\Platform_ast_controller_staff@generateDesktopShortcut');
            Route::post('icon-dragevent', 'Staff\Astracking\Platform_ast_controller_staff@captureNumberOfDragEvent');

#------------------------Export view report & plans----------------------#
            Route::any('export-pupil-action-plans', 'Staff\Astracking\Platform_ast_controller_staff@exportPupilActionPlans');
            Route::any('export-pupil-action-plans-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@pupilActionPlansDataAjax');
            Route::get('download-report/{string?}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@pupilActionPlansDownload');
            Route::post('download-report-zip', 'Staff\Astracking\Platform_ast_controller_staff@pupilActionPlansZipDownload');
            Route::post('group-report-zip-download', 'Staff\Astracking\Platform_ast_controller_staff@groupReportZipDownload');
            Route::any('download-zip/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@zipDownload');


#------------------------Export group view report & plans----------------------#
            Route::any('group/download-zip/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@groupZipDownload');
            Route::any('export-group-action-plan', 'Staff\Astracking\Platform_ast_controller_staff@exportGroupActionPlans');
            Route::any('export-group-action-plans-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@groupActionPlansDataAjax');

        });

        #-------L-3 L-5 Only-----------------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|senior-practitioner)'],
            'middleware' => 'level'
                ], function() {

        });


        #-------L-3 L-4 L-5 and L6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner)|consultant'],
            'middleware' => 'level'
                ], function() {

            #---------------------- Forthcoming Training --------------------------
            Route::get('res-training-courses', 'Staff\Astracking\Platform_ast_controller_staff@resTrainingCourses');

            #----------------------Staff Activity--------------------------
            Route::get('staff-activity', 'Staff\Astracking\Platform_ast_controller_staff@staffActivity');
            Route::post('staff-activity-ajax', 'Staff\Astracking\Platform_ast_controller_staff@staffActivityAjax');

            # ---------------- Resources  ------------------
            Route::get('technical-resources', 'Staff\Astracking\Platform_ast_controller_staff@technicalResources');

            Route::get('platform-ast', 'Staff\Astracking\Platform_ast_controller_staff@platformAstAdmin');
            Route::post('switch-aet', 'Staff\Astracking\Platform_ast_controller_staff@storeAetSession');
            Route::get('platform-ast-menu', 'Staff\Astracking\Platform_ast_controller_staff@platformAstMenu');
//            Route::post('getOverviewSchool', 'Staff\Astracking\Platform_ast_controller_staff@getOverviewSchool');
            Route::post('update-group-dashboard', 'Staff\Astracking\Platform_ast_controller_staff@UpdateOverviewSchool');

            Route::get('platform-ast-mobile-apps', 'Staff\Astracking\Platform_ast_controller_staff@platformAstMobileApps');

            #---------------------- multischool login --------------------------
            Route::get('multischool', 'Staff\Astracking\Platform_ast_controller_staff@selectMultischool');
            Route::get('multischool-login', 'Common\Login_controller@multischoolLogin');
            Route::get('groupplatform-login', 'Common\Login_controller@groupplatformLogin');
            Route::post('multischool-list', 'Staff\Astracking\Platform_ast_controller_staff@get_multischool_list');
            Route::get('get_gd_data/{id?}', 'Staff\Astracking\Platform_ast_controller_staff@groupDashboardData');
            Route::post('getOverviewSchool', 'Staff\Astracking\Platform_ast_controller_staff@getOverviewSchool');

            #------------------------Export Login----------------------#
            Route::any('export-logins', 'Staff\Astracking\Platform_ast_controller_staff@exportLogins');
            Route::any('export-logins-data', 'Staff\Astracking\Platform_ast_controller_staff@getExportLoginsData');
            Route::post('export-logins-csv-pdf-excel-data', 'Staff\Astracking\Platform_ast_controller_staff@exportLoginsCsvPdfExcelData');
            Route::post('export-logins-save-pdf', 'Staff\Astracking\Platform_ast_controller_staff@exportLoginSavePdf');

            #------------------------Deletion of Half Data----------------------#
            Route::any('deletion-of-half-assessment', 'Staff\Astracking\Platform_ast_controller_staff@deletionOfHalfAssessment');
            Route::any('half-assessment-data', 'Staff\Astracking\Platform_ast_controller_staff@getHalfAssessmentData');
            Route::post('delete-halfassessment', 'Staff\Astracking\Platform_ast_controller_staff@deleteHalfassessment');

            #------------------------Online program----------------------#
            Route::get('online-training-programme', 'Staff\Astracking\Platform_ast_controller_staff@onlineTrainingProgramme');
            Route::get('staff-training-progress', 'Staff\Astracking\Platform_ast_controller_staff@staffTrainingProgress');
            Route::get('training-ajax', 'Staff\Astracking\Platform_ast_controller_staff@trainingAjax');
            Route::post('remove-training-progress-data', 'Staff\Astracking\Platform_ast_controller_staff@deleteTrainingProgressHistory');

            #------------------------Assessment ON/Off----------------------#
            Route::any('assessment-on-off', 'Staff\Astracking\Platform_ast_controller_staff@assementOnOffSwitch');
            Route::post('check-assessment-status', 'Staff\Astracking\Platform_ast_controller_staff@checkAssessmentStatus');
            Route::post('update-assessment-status', 'Staff\Astracking\Platform_ast_controller_staff@updateAssessmentOnOff');
            Route::post('update-browser-session-status', 'Staff\Astracking\Platform_ast_controller_staff@updateBrowserSessionOnOff');
            Route::post('store-session-code-for-browser', 'Staff\Astracking\Platform_ast_controller_staff@insertSessionCode');

            #------------------------ Ast APP L-5 ----------------------
            Route::any('generate-session-code', 'Staff\Astracking\Platform_ast_controller_staff@generateSessionCode');
            Route::post('store-session-code', 'Staff\Astracking\Platform_ast_controller_staff@storeSessionCode');
            Route::post('get-random-session-code', 'Staff\Astracking\Platform_ast_controller_staff@getRandomSessionCode');

            #------------------------ Edit password ----------------------
            Route::match(['get', 'post'], 'edit-password', 'Common\Password_controller@editPassword_step1');
            Route::get('check-password/{string?}', 'Common\Password_controller@checkPassword');
            Route::post('edit-password-step2', 'Common\Password_controller@editPasswordStep2');

            #------------------------ Destopicon  ----------------------
            Route::get('desktop-shortcut', 'Staff\Astracking\Platform_ast_controller_staff@generateDesktopShortcut');
            Route::post('icon-dragevent', 'Staff\Astracking\Platform_ast_controller_staff@captureNumberOfDragEvent');

            #------------------------ Yet to complete  ----------------------
            Route::get('yet-to-complete-tile', 'Staff\Astracking\Cohort\Cohort_page_controller@yetToCompletePupil');
            Route::any('yet-to-complete-tile-csv/{type?}', 'Staff\Astracking\Cohort\Cohort_page_controller@yetToCompletePupilCSVExport');

            #------------------------ Take the Wheel  ----------------------
            Route::get('take-the-wheel', 'Staff\Astracking\Platform_ast_controller_staff@take_the_wheel');
            Route::post('take-the-wheel-status', 'Staff\Astracking\Platform_ast_controller_staff@take_the_wheel_status');

            # ---------------- Online training program  ------------------
            //Route::get('platform-ast-menu', 'Staff\Astracking\Platform_ast_controller_staff@platformAstMenu');
            Route::get('splash-screen', 'Staff\Astracking\Platform_ast_controller_staff@astSplashScreen');
            Route::get('platform-ast-training', 'Staff\Astracking\Platform_ast_controller_staff@astTrainingVideo');
            Route::post('update-training-module-list', 'Staff\Astracking\Platform_ast_controller_staff@updateTrainingModuleList');
            Route::post('update-staff-training-module-list', 'Staff\Astracking\Platform_ast_controller_staff@updateAllStaffTrainingModuleList');
            //Route::post('platform-ast-training/{flag}/update-training-module-list', 'Staff\Astracking\Platform_ast_controller_staff@platformAstMenu');
            # ---------------- get tutorial video ------------------
            Route::get('get-tutorial/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@tutorialVideo');
            Route::get('get-risk-image/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@riskImage');
            Route::post('watched-tutorial', 'Staff\Astracking\Platform_ast_controller_staff@watchedTutorial');
            Route::post('add-tooltip-status', 'Staff\Astracking\Platform_ast_controller_staff@addTooltipStatus');
            Route::post('add-tutorial-status', 'Staff\Astracking\Platform_ast_controller_staff@addTutorialStatus');
            Route::post('get-tutorial-status', 'Staff\Astracking\Platform_ast_controller_staff@getTutorialDetails');

            #------------------------Export group view report & plans----------------------#
            Route::post('group-report-download', 'Staff\Astracking\Platform_ast_controller_staff@groupReportDownload');
            Route::get('report-view/{type}/{id}/{string}', 'Staff\Astracking\Platform_ast_controller_staff@reportView');

            #------------------------ Export pupil action plans ----------------------#
            Route::get('download-actionplan/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadActionplan');
            Route::get('send-actionplan/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadActionplan');

            #------------------------Export view report & plans----------------------#
            Route::any('export_pupil_score_csv', 'Staff\Astracking\Platform_ast_controller_staff@exportPupilScoreCsv');
            Route::post('export-score-ajax', 'Staff\Astracking\Platform_ast_controller_staff@exportPupilScoreCsvAjax');
            Route::post('export-csv', 'Staff\Astracking\Platform_ast_controller_staff@exportCsv');

            #---------------------- Animation video --------------------------
            Route::get('animation-video', 'Staff\Astracking\Platform_ast_controller_staff@animationVideo');
            Route::get('trial-assessment', 'Staff\Astracking\Platform_ast_controller_staff@trialAssessmentView');

        });


        #-------L-3 L-5 L-6 -----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {
            Route::get('calendar-list', 'Staff\Astracking\Platform_ast_controller_staff@schedulerSelect');
            Route::post('scheduler-list-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@schedulerList');

            # ---------------- pupil-data add/edit/delete  ------------------
            Route::get('pupil-data', 'Staff\Astracking\Platform_ast_controller_staff@pupilDataView');
            Route::post('pupil-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@pupilDataAjax');
            Route::post('onoff-switch-ajax', 'Staff\Astracking\Platform_ast_controller_staff@onOffSwitchAjax');
            Route::any('add-pupil-view', 'Staff\Astracking\Platform_ast_controller_staff@addPupilView');
            Route::get('edit-pupil-view-{id}', 'Staff\Astracking\Platform_ast_controller_staff@editPupilView');
            Route::any('edit-pupil-data', 'Staff\Astracking\Platform_ast_controller_staff@editPupildata');
            Route::post('add-pupil-data', 'Staff\Astracking\Platform_ast_controller_staff@addPupilData');
            Route::post('delete-pupil', 'Staff\Astracking\Platform_ast_controller_staff@deletePupil');
            Route::get('check-username/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@checkUsername');
            Route::get('check-mis-id/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@checkMisId');
            Route::get('export-pupils-data', 'Staff\Astracking\Platform_ast_controller_staff@exportPupilData');

            #------------------------Edit Staff Data & Permission----------------------
            Route::any('staff-data', 'Staff\Astracking\Platform_ast_controller_staff@StaffData');
            Route::any('staff-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@staffDataAjax');
            Route::any('add-staff-view', 'Staff\Astracking\Platform_ast_controller_staff@addStaffDataView');
            Route::any('add-staff-data', 'Staff\Astracking\Platform_ast_controller_staff@storeStaffData');
            Route::any('edit-staff-view/{id}', 'Staff\Astracking\Platform_ast_controller_staff@editstaffView');
            Route::any('edit-staff-data', 'Staff\Astracking\Platform_ast_controller_staff@editstaffdata');
            Route::any('delete-staff-data', 'Staff\Astracking\Platform_ast_controller_staff@deletestaffdata');
            Route::any('check-unique/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@checkUniqueForStaff');
            Route::any('lead-sp', 'Staff\Astracking\Platform_ast_controller_staff@leadSp');
            Route::any('update-lead-sp', 'Staff\Astracking\Platform_ast_controller_staff@updateLeadSp');
            Route::post('update-lead-sp-status', 'Staff\Astracking\Platform_ast_controller_staff@updateLeadSpStatus');
            Route::any('remove-mistakenly-data', 'Staff\Astracking\Platform_ast_controller_staff@removeMistakenlyData');
            Route::any('remove-mistakenly-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@removeMistakenlyDataAjax');
            Route::any('aditional-data-view', 'Staff\Astracking\Platform_ast_controller_staff@aditionalDataView');
            Route::any('save-houses', 'Staff\Astracking\Platform_ast_controller_staff@saveHouses');
            Route::any('save-years', 'Staff\Astracking\Platform_ast_controller_staff@saveYears');
            Route::get('export-staff-logins', 'Staff\Astracking\Platform_ast_controller_staff@exportStaffLogins');

            # ---------------- Forward Ast Data  ------------------
            Route::get('forward-ast-data', 'Staff\Astracking\Transition\Send\Transitions@forwardAstData');
            Route::get('select-school-ajax/{string?}', 'Staff\Astracking\Transition\Send\Transitions@selectSchoolAjax');
            Route::post('selecte-school-view', 'Staff\Astracking\Transition\Send\Transitions@selecteSchoolView');
            Route::post('send-pupil-data', 'Staff\Astracking\Transition\Send\Transitions@sendPupilData');
            Route::post('complete-pupil-data', 'Staff\Astracking\Transition\Send\Transitions@completePupilData');
            Route::post('complete-pupil-data-ajax', 'Staff\Astracking\Transition\Send\Transitions@completeDataAjax');
            Route::post('retrieve-pupil-data-ajax', 'Staff\Astracking\Transition\Send\Transitions@retrievePupilDataAjax');
            Route::post('forward-send-mail-data-ajax', 'Staff\Astracking\Transition\Send\Transitions@forwardMailAjax');
            Route::post('forward-pupil-name-ajax', 'Staff\Astracking\Transition\Send\Transitions@pupilNameAjax');
            Route::post('terminate-pupil', 'Staff\Astracking\Transition\Send\Transitions@terminatePupil');
            Route::post('send-mail', 'Staff\Astracking\Transition\Send\Transitions@sendMail');
            Route::post('forward-mail-send', 'Staff\Astracking\Transition\Send\Transitions@forwardMailSend');
            Route::post('forward-send-mail-from', 'Staff\Astracking\Transition\Send\Transitions@forwardSendMailFrom');
            Route::post('resend-request', 'Staff\Astracking\Transition\Send\Transitions@resendRequest');
            Route::post('prepare-ast-pupil-data', 'Staff\Astracking\Transition\Send\Transitions@prepareToSendPupData');
            Route::post('send-data', 'Staff\Astracking\Transition\Send\Transitions@sendData');
            Route::post('decline-data', 'Staff\Astracking\Transition\Send\Transitions@declineData');
            Route::post('header-template', 'Staff\Astracking\Transition\Send\Transitions@headerTemplate');

            # ----------------  Request Ast Data  ------------------
            Route::get('request-ast-data', 'Staff\Astracking\Transition\Send\Transitions@requestAstData');
            Route::post('select-school-view', 'Staff\Astracking\Transition\Send\Transitions@selectSchoolView');
            Route::post('receive-pupil-data', 'Staff\Astracking\Transition\Send\Transitions@selecteReceivePupilData');
            Route::post('receive-pupil-ast-data', 'Staff\Astracking\Transition\Send\Transitions@receivePupilAstData');
            Route::post('receive-ast-data-ajax', 'Staff\Astracking\Transition\Send\Transitions@receiveDataAjax');
            Route::post('receive-send-mail-data-ajax', 'Staff\Astracking\Transition\Send\Transitions@receiveMailAjax');
            Route::post('request-mail-send', 'Staff\Astracking\Transition\Send\Transitions@requestMailSend');
            Route::post('request-send-mail-from', 'Staff\Astracking\Transition\Send\Transitions@requestSendMailFrom');
            Route::post('rec-approve-data', 'Staff\Astracking\Transition\Send\Transitions@recApproveData');
            Route::post('rec-resend-data', 'Staff\Astracking\Transition\Send\Transitions@recResendRequest');
            Route::post('rec-deny-data', 'Staff\Astracking\Transition\Send\Transitions@recDenyRequest');

            # ----------------  Pupil Data Connection  ------------------
            Route::get('pupil-data-connection', 'Staff\Astracking\Platform_ast_controller_staff@PupilDataConnection');
            Route::post('sponsor-school', 'Staff\Astracking\Platform_ast_controller_staff@getSponsoredPupil');
            Route::post('collect', 'Staff\Astracking\Platform_ast_controller_staff@getCollect');
            Route::post('check', 'Staff\Astracking\Platform_ast_controller_staff@getcheck');
            Route::get('update/{string?}', 'Staff\Astracking\Platform_ast_controller_staff@transferSponsoreAssessmentData');

            #------------------------Import-tutorial----------------------#
            Route::any('import-tutorial', 'Staff\Astracking\Platform_ast_controller_staff@importTutorial');
            Route::any('import-tutorial-data-ajax', 'Staff\Astracking\Platform_ast_controller_staff@importTutorialDataAjax');

            #---------------------- Import pupil data --------------------------
            Route::get('import-pupil-data', 'Staff\Astracking\Import\Import_pupil_controller@importPupilData');
            Route::get('selected-option{option?}', 'Staff\Astracking\Import\Import_pupil_controller@selectedOption');
            Route::get('import_tutorial', 'Staff\Astracking\Import\Import_pupil_controller@importPupilTutorial');
            Route::get('all-user-export-csv', 'Staff\Astracking\Import\Import_pupil_controller@allUserExportCsv');
            Route::get('custom-export-csv', 'Staff\Astracking\Import\Import_pupil_controller@exportCsv');
            Route::get('step2_uploadcsv-pupil-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@step2UploadCsv');
            Route::post('step2_UploadSelectedCSV-{option}', 'Staff\Astracking\Import\Import_pupil_controller@step2UploadSelectedCSV');
            Route::get('step2_Set_DT_Formate-{option}', 'Staff\Astracking\Import\Import_pupil_controller@step2SetDtFormate');
            Route::post('step2-check-date-formate', 'Staff\Astracking\Import\Import_pupil_controller@step2CheckDateFormate');
            Route::get('step3_matching-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@step3Matching');
            Route::post('setnewmatching-{option}', 'Staff\Astracking\Import\Import_pupil_controller@setNewMatchingData');
            Route::get('step3-check-temp-{option}', 'Staff\Astracking\Import\Import_pupil_controller@stepCheckTemp');
            Route::get('edit-checking-pupil-name-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@editCheckingPupilName');
            Route::post('edit-changes-del-row', 'Staff\Astracking\Import\Import_pupil_controller@editChangesDelRow');
            Route::post('edit-changes-value', 'Staff\Astracking\Import\Import_pupil_controller@editChangesValue');
            Route::get('name-matching-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@nameMatching');
            Route::get('display-name-matching-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@displayNameMatching');
            Route::get('save-data-{option}', 'Staff\Astracking\Import\Import_pupil_controller@saveData');
            Route::get('school-profile-{Myoption}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@schoolProfile');
            Route::post('final-save-data-pupil', 'Staff\Astracking\Import\Import_pupil_controller@finalSaveData');
            Route::post('update-new-mapped-{updatedatastep}-{option}', 'Staff\Astracking\Import\Import_pupil_controller@setMappCustomData');
            Route::get('additional-information-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@additionalInformation');
            Route::get('custom-fields-{option}-{myUserOption}', 'Staff\Astracking\Import\Import_pupil_controller@customFields');

            #----------- wonde import
            Route::get('wonde-import', 'Staff\Astracking\Import\Import_pupil_controller@wondeImport');
            Route::get('show-wonde-compare-data', 'Staff\Astracking\Import\Import_pupil_controller@showWondeCompareData');
            Route::post('wonde-import-form-data', 'Staff\Astracking\Import\Import_pupil_controller@wondeImportFormData');
            Route::post('wonde-import-data', 'Staff\Astracking\Import\Import_pupil_controller@wondeImportData');
            Route::post('send-wonde-import-mail', 'Staff\Astracking\Import\Import_pupil_controller@sendWondeImportMail');
            Route::post('check-wonde-with-pop', 'Staff\Astracking\Import\Import_pupil_controller@checkWondeWithPop');
            Route::post('check-multi-wonde-id', 'Staff\Astracking\Import\Import_pupil_controller@checkMultiWondeId');
            Route::post('check-wonde-duplicate-username', 'Staff\Astracking\Import\Import_pupil_controller@checkWondeDuplicateUsername');
            Route::post('check-if-email-null', 'Staff\Astracking\Import\Import_pupil_controller@findNullStudentemails');

            #----------- Import Staff Data -------------
            Route::get('import-staff-data', 'Staff\Astracking\Import\Import_staff_controller@importStaffData');
            Route::get('step1-initialcheck-{option}', 'Staff\Astracking\Import\Import_staff_controller@step1IntialCheck');
            Route::get('step2-uploadcsv-{option}', 'Staff\Astracking\Import\Import_staff_controller@step2UploadCsv');
            Route::post('staff-step2-uploadselectedCSV-{option}', 'Staff\Astracking\Import\Import_staff_controller@staffStep2UploadSelectedCsv');
            Route::get('step3-staffprofile-{option}-{mypwd}', 'Staff\Astracking\Import\Import_staff_controller@staffStep3StaffProfile');
            Route::post('staff-setnewmatching-{option}', 'Staff\Astracking\Import\Import_staff_controller@staffStep3Setnewmatch');
            Route::get('staff-step3-check-temp-{option}', 'Staff\Astracking\Import\Import_staff_controller@staffStep3Checktemp');
            Route::get('edit-checking-staff-name-{Myoption}-{Mypwd}', 'Staff\Astracking\Import\Import_staff_controller@editCheckingStaff');
            Route::post('staff-edit-changes-data', 'Staff\Astracking\Import\Import_staff_controller@staffEditChangeData');
            Route::post('staff-csv-del-row', 'Staff\Astracking\Import\Import_staff_controller@staffEditChangeDelete');
            Route::get('staff-name-matching-{option}-{Mypwd}', 'Staff\Astracking\Import\Import_staff_controller@staffNameMatching');
            Route::post('match-update-data', 'Staff\Astracking\Import\Import_staff_controller@staffMatchUpdateData');
            Route::get('display-name-matching-{option}', 'Staff\Astracking\Import\Import_staff_controller@staffDisplayNameMatching');
            Route::get('save-step-{option}', 'Staff\Astracking\Import\Import_staff_controller@staffSaveStep');
            Route::post('final-save-data', 'Staff\Astracking\Import\Import_staff_controller@staffFinalSaveData');

            Route::get('wonde-import-staff', 'Staff\Astracking\Import\Import_staff_controller@wondeStaffImport');
            Route::post('check-wonde-staff-with-pop', 'Staff\Astracking\Import\Import_staff_controller@checkWondeStaffWithPop');
            Route::post('show-wonde-staff-compare-data', 'Staff\Astracking\Import\Import_staff_controller@showWondeStaffCompareData');
            Route::get('wonde-staff-import-process', 'Staff\Astracking\Import\Import_staff_controller@wondeStaffImportProcess');
            Route::post('wonde-import-staff-data', 'Staff\Astracking\Import\Import_staff_controller@wondeStaffImportData');
            Route::post('check-staff-multi-wonde-id', 'Staff\Astracking\Import\Import_staff_controller@checkMultiWondeId');
            Route::post('check-wonde-duplicate-staff-username', 'Staff\Astracking\Import\Import_staff_controller@checkWondeDuplicateStaffUsername');
        });


        #-------L-3 L-5 L-6 and L7 Only-----------
        Route::group([
                    'prefix' => '{level}',
                    'where' => ['level' => '(it-staff|senior-practitioner|consultant|admin)'],
                    'middleware' => 'level'
                ], function() {
                            Route::any('permissionindex', 'Staff\Astracking\Platform_ast_controller_staff@permissionIndex');
                            Route::any('permission', 'Staff\Astracking\Platform_ast_controller_staff@permission');
                            Route::post('export-staff-permission', 'Staff\Astracking\Platform_ast_controller_staff@exportStaffPermission');
                            Route::post('save-new-permission', 'Staff\Astracking\Platform_ast_controller_staff@saveNewPermission');

                            Route::group(['middleware' => ['xss']], function () {
                                Route::any('platform-planning/{string?}', 'Staff\Astracking\Planning\Planning_controller@planning');
                                Route::any('platform-planner/{string?}', 'Staff\Astracking\Planning\Planning_controller@planner');
                                Route::post('save-planner', 'Staff\Astracking\Planning\Planning_controller@savePlanner');
                                Route::post('save-event', 'Staff\Astracking\Planning\Planning_controller@saveEvent');
                                Route::get('planner-scheduler/{string?}', 'Staff\Astracking\Planning\Planning_controller@plannerScheduler');
                                Route::get('scheduler-list/{string?}', 'Staff\Astracking\Planning\Planning_controller@schedulerList');
                                Route::post('index-proforma', 'Staff\Astracking\Planning\Planning_controller@IndexProforma');
                                Route::post('planning-resources', 'Staff\Astracking\Planning\Planning_controller@planningResources');
                                Route::post('planning-send-mail', 'Staff\Astracking\Planning\Planning_controller@planningSendMail');
                            });
                        });



                #------- L3 L-4 L-5 and L-6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {

            # ************** Filters Popup ************* #
            Route::get('select-filters', 'Staff\Astracking\Analytics\Filters_controller@filters');
            Route::any('cohort-filters', 'Staff\Astracking\Cohort\Filters_controller@filters');
            Route::post('cohort-pupil-list', 'Staff\Astracking\Cohort\Filters_controller@getAutoPupilList');
            Route::post('search-pupil', 'Staff\Astracking\Cohort\Filters_controller@searchPupil');
            Route::get('pupil-search-result', 'Staff\Astracking\Cohort\Filters_controller@searchResult');
            Route::get('pupil-visuals', 'Staff\Astracking\Cohort\Pupil_tracking_controller@visuals');
            Route::post('visuals-score', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getGenConScore');

            Route::any('risk-descriptors', 'Staff\Astracking\Cohort\Pupil_tracking_controller@riskDdescriptors');
            Route::get('tracking-chart', 'Staff\Astracking\Cohort\Pupil_tracking_controller@TrackingChart');
            Route::get('moniter', 'Staff\Astracking\Cohort\Pupil_tracking_controller@moniter');
            Route::post('add-moniter', 'Staff\Astracking\Cohort\Pupil_tracking_controller@addMoniter');
            Route::get('route-map', 'Staff\Astracking\Cohort\Pupil_tracking_controller@routeMap');

            Route::any('family-signpost', 'Staff\Astracking\Cohort\Pupil_tracking_controller@familySignpost');
            Route::post('get-family-signpost', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getFamilySignpost');
            Route::post('save-familypost', 'Staff\Astracking\Cohort\Pupil_tracking_controller@saveFamilySignpost');
            Route::post('get-familypost-factor-list', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getFamilypostFactorList');
            Route::get('save-family-action-plan/{id}/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadPdfFp');
            Route::get('view-family-actionplan/{id}/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadPdfFp');
            Route::get('send-signposts-mail/{id}/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadPdfFp');
            Route::post('edit-familysignposts', 'Staff\Astracking\Cohort\Pupil_tracking_controller@editfamilySignpostsPdf');
            Route::post('update-family-signposts', 'Staff\Astracking\Cohort\Pupil_tracking_controller@updateFamilySignposts');
            Route::post('family-signposts-delete-pdf', 'Staff\Astracking\Cohort\Pupil_tracking_controller@familySignpostsdeletePdf');

            Route::post('select-last-filters', 'Staff\Astracking\Cohort\Filters_controller@lastFilterResult');
            Route::post('cohort-page/{id?}', 'Staff\Astracking\Cohort\Cohort_page_controller@cohort');

            Route::get('cohort-data-page/{id?}', 'Staff\Astracking\Cohort\Cohort_page_controller@cohortData');
            Route::post('polar-bais-gen-con-ajax', 'Staff\Astracking\Cohort\Cohort_page_controller@polarBaisGenConAjax');
            Route::post('left-pupil', 'Staff\Astracking\Cohort\Cohort_page_controller@leftPupil');
            Route::post('reflect', 'Staff\Astracking\Cohort\Cohort_page_controller@reflect');

            Route::any('cohort-plans', 'Staff\Astracking\Cohort\Cohort_page_controller@cohortplans');
            Route::post('delete-pdf', 'Staff\Astracking\Cohort\Cohort_page_controller@deletePdf');
            Route::post('send-cohort-pdf-mail', 'Staff\Astracking\Cohort\Cohort_page_controller@sendCohortPdfMail');

            Route::post('select-years', 'Staff\Astracking\Cohort\Filters_controller@getFilterDataWithPermission');
            Route::post('delete-search-history', 'Staff\Astracking\Cohort\Filters_controller@deleteSearchHistory');

            Route::get('cohort-report', 'Staff\Astracking\Cohort\Cohort_report_controller@index');
            Route::get('cohort-report/{id?}', 'Staff\Astracking\Cohort\Cohort_report_controller@index');
            Route::post('save-report-pdf', 'Staff\Astracking\Cohort\Cohort_report_controller@cohort_report_pdf');
            Route::post('generate-report-pdf', 'Staff\Astracking\Cohort\Cohort_report_controller@generate_cohort_report_pdf');
            Route::post('teacher-search', 'Staff\Astracking\Cohort\Cohort_report_controller@getEmailList');
            Route::post('cohort-report-send-mail', 'Staff\Astracking\Cohort\Cohort_report_controller@SendReportInEmail');

            Route::post('get-acp-dd-report', 'Staff\Astracking\Cohort\Cohort_page_controller@getAcpDdReport');
            Route::post('edit-get-acp-dd-report', 'Staff\Astracking\Cohort\Cohort_page_controller@editGetAcpDdReport');
            Route::post('edit-acp-dd-report', 'Staff\Astracking\Cohort\Cohort_page_controller@editAcpDdReport');
            Route::post('get-factor-wise-statement', 'Staff\Astracking\Cohort\Cohort_page_controller@getFactorWiseStatement');
            Route::post('check-report-complete', 'Staff\Astracking\Cohort\Cohort_page_controller@checkReportComplete');
            Route::post('step2-cohort-acp', 'Staff\Astracking\Cohort\Cohort_page_controller@step2AcpReport');
            Route::post('cohort-saveddata-groupreport', 'Staff\Astracking\Cohort\Cohort_page_controller@cohortSaveDataGroupreport');
//            Route::post('save-chart-pdf', 'Staff\Astracking\Cohort\Cohort_page_controller@saveChartPdf');
            Route::post('teacher-email-list', 'Staff\Astracking\Cohort\Cohort_page_controller@getTeacherEmailList');
            Route::post('delete-selected-pdf', 'Staff\Astracking\Cohort\Cohort_page_controller@deleteSeletcedPdfReport');

            //Group Action plan routes
            Route::post('get-current_group-acp-dd-report', 'Staff\Astracking\Cohort\Cohort_page_controller@getCurrentGroupAcpDdReport');
            Route::post('write-group-acp-dd-report', 'Staff\Astracking\Cohort\Cohort_page_controller@writeGroupAcpDdReport');
            Route::post('get-past-gap-cap', 'Staff\Astracking\Cohort\Cohort_page_controller@getPastGapCap');

            //End Group Action plan routes
            Route::post('action-plan-overview', 'Staff\Astracking\Cohort\Cohort_page_controller@actionPlanOverview');
            Route::post('save-signpost-detail', 'Staff\Astracking\Cohort\Cohort_page_controller@saveSignpostDetail');
            Route::post('save-note-detail', 'Staff\Astracking\Cohort\Cohort_page_controller@saveNoteDetail');
            Route::post('acp-overview-save-pdf', 'Staff\Astracking\Cohort\Cohort_page_controller@acpOverviewSavePdf');
            Route::post('delete-saved-file', 'Staff\Astracking\Cohort\Cohort_page_controller@deleteSavedFile');
            Route::post('acp-overview-save-csvfile', 'Staff\Astracking\Cohort\Cohort_page_controller@acpOverviewSaveCsvfile');
            Route::post('review-monitor-comments', 'Staff\Astracking\Cohort\Cohort_page_controller@reviewMonitorComments');
            Route::post('review-group-actionplan', 'Staff\Astracking\Cohort\Cohort_page_controller@reviewGroupActionplan');
            Route::post('review-cohort-actionplan', 'Staff\Astracking\Cohort\Cohort_page_controller@reviewCohortActionplan');
            Route::post('save-author-name', 'Staff\Astracking\Cohort\Cohort_page_controller@saveAuthorName');

            Route::post('pupil-action-plans', 'Staff\Astracking\Cohort\Cohort_page_controller@pupilActionPlans');
            Route::post('delete-pupil-actionplan-pdf', 'Staff\Astracking\Cohort\Cohort_page_controller@deletePupilActionplanPdf');
            Route::get('download-pupil-report', 'Staff\Astracking\Cohort\Cohort_page_controller@downloadPupilReport');
            Route::post('unlink-file', 'Staff\Astracking\Platform_ast_controller_staff@unlinkFile');
            #************ End Filters Popup *************#

            Route::post('cohort-yet-to-complete', 'Staff\Astracking\Cohort\Cohort_page_controller@commonYetComplete');
            Route::get('cohort-yet-to-completed-csv', 'Staff\Astracking\Cohort\Cohort_page_controller@commonYetCompleteCSVExport');

            #************ As tracking Actionplan*************#
            Route::any('actionplan', 'Staff\Astracking\Cohort\Pupil_tracking_controller@actionplan');
            Route::post('save-actionplan', 'Staff\Astracking\Cohort\Pupil_tracking_controller@saveActionplan');
            Route::post('actionplan-data', 'Staff\Astracking\Cohort\Pupil_tracking_controller@actionplanData');
            Route::post('comments', 'Staff\Astracking\Cohort\Pupil_tracking_controller@comments');
            Route::post('add-comment', 'Staff\Astracking\Cohort\Pupil_tracking_controller@addComment');
            Route::post('get-polar-bias', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getPolarBias');
            Route::post('get-causes-risks-signpost', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getCausesRisksSignpost');
            Route::post('get-authorname-pdf-name', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getPdfAndAuthorName');
            Route::post('get-factor-list', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getFactorList');
            Route::post('export-chart-image', 'Staff\Astracking\Cohort\Pupil_tracking_controller@exportChartImage');
            Route::post('pupil-tracking-delete-pdf', 'Staff\Astracking\Cohort\Pupil_tracking_controller@deletePdf');
            Route::get('download-pdf', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadPdf');
            Route::post('edit-actionplan', 'Staff\Astracking\Cohort\Pupil_tracking_controller@editPdf');
            Route::post('update-pupil-actionplan', 'Staff\Astracking\Cohort\Pupil_tracking_controller@updatePupilActionplan');
            Route::post('generate-pdf', 'Staff\Astracking\Cohort\Pupil_tracking_controller@generateExportPdf');
            Route::get('export-tab', 'Staff\Astracking\Cohort\Pupil_tracking_controller@exportTab');
            Route::get('review', 'Staff\Astracking\Cohort\Pupil_tracking_controller@review');
            Route::post('review-edit-save', 'Staff\Astracking\Cohort\Pupil_tracking_controller@reviewEditSave');
            Route::post('copy-onenote', 'Staff\Astracking\Cohort\Pupil_tracking_controller@copyOneNote');
            Route::post('check-plan-exist', 'Staff\Astracking\Cohort\Pupil_tracking_controller@checkPlanExist');
            Route::get('view-actionplan-browser/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@downloadActionplan');
            Route::post('past-tracking-chart', 'Staff\Astracking\Cohort\Pupil_tracking_controller@getTrackingChart');
            Route::get('view-actionplan/{id}/{year}/{string}', 'Staff\Astracking\Cohort\Pupil_tracking_controller@viewApInBrowser');

            #------------------Priority & crisk pupil report ----------------------#
            Route::post('export-priority-pupil', 'Staff\Astracking\Platform_ast_controller_staff@exportPriorityPupil');
            Route::post('priority-pupil-save-pdf', 'Staff\Astracking\Platform_ast_controller_staff@priorityPupilSavePdf');
            Route::post('priority-pupil-save-csvfile', 'Staff\Astracking\Platform_ast_controller_staff@priorityPupilSaveCsvfile');

            Route::post('export-composite-risk-pupil', 'Staff\Astracking\Platform_ast_controller_staff@exportCompositeRiskPupil');
            Route::post('composite-risk-pupil-save-pdf', 'Staff\Astracking\Platform_ast_controller_staff@compositeRiskPupilSavePdf');
            Route::post('composite-risk-pupil-save-csvfile',  'Staff\Astracking\Platform_ast_controller_staff@compositeRiskPupilSaveCsvfile');

            #************ End As tracking *************#
        });

        #-------L-5 -----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(senior-practitioner)'],
            'middleware' => 'level'
                ], function() {


        });

        #-------L-5 L-6-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {
            # ---------------- staff-activity  ------------------
            Route::get('staff-last-login', 'Staff\Astracking\Platform_ast_controller_staff@staffLastLogin');
            Route::post('staff-last-login-ajax', 'Staff\Astracking\Platform_ast_controller_staff@staffLastLoginAjax');



# ---------------- Accreditation  ------------------
            Route::get('accreditation', 'Staff\Astracking\Platform_ast_controller_staff@accreditation');
            Route::post('accreditation-recorde', 'Staff\Astracking\Platform_ast_controller_staff@accreditationRecorde');
            Route::post('check-goldstamp-accred', 'Staff\Astracking\Platform_ast_controller_staff@checkGoldstampAccreditation');
            Route::any('update-stamp/{id}/{stamp}/{type}', 'Staff\Astracking\Platform_ast_controller_staff@updateStamp');
            Route::post('ajax-action-proforma', 'Staff\Astracking\Platform_ast_controller_staff@ajaxActionProforma');


            #************Analytics Charts ***********//

            Route::get('analytics-chart', 'Staff\Astracking\Analytics\Chart_controller@displayChartIndex');
            Route::get('display-chart', 'Staff\Astracking\Analytics\Chart_controller@display_chart');
            Route::post('get-campus-data', 'Staff\Astracking\Analytics\Chart_controller@getCampusData');

# ----------- priority composite pupil ------------
            Route::get('priority-composite-pupil', 'Staff\Astracking\Analytics\Priocomposite_controller@priorityCompositePupil');
            Route::post('priority-composite-pupil-ajax', 'Staff\Astracking\Analytics\Priocomposite_controller@priorityCompositePupilAjax');

# ----------- priority pupil year on year -----------
            Route::get('w-priority-pupils', 'Staff\Astracking\Analytics\Priority_pupil_controller@wPriorityPupils');
            Route::post('w-priority-pupils-ajax', 'Staff\Astracking\Analytics\Priority_pupil_controller@priorityPupilAjax');

# ---------- Composite risks accross the school --------
            Route::get('composite-risk-school', 'Staff\Astracking\Analytics\Composite_risks_controller@compositeRisksSchool');
            Route::post('composite-risk-school-ajax', 'Staff\Astracking\Analytics\Composite_risks_controller@compositeRisksSchoolAjax');

#---------- Factor MeanScore ----------
            Route::get('factor-meanscore', 'Staff\Astracking\Analytics\Factor_meanscore_controller@factorMeanscore');
            Route::post('factor-meanscore-ajax', 'Staff\Astracking\Analytics\Factor_meanscore_controller@factorMeanscoreAjax');

#---------- Polarbias age gender -----------
            Route::get('polarbias-age-gender', 'Staff\Astracking\Analytics\Polarbias_age_gender_controller@polarbiasAgeGender');
            Route::post('polarbias-age-gender-ajax', 'Staff\Astracking\Analytics\Polarbias_age_gender_controller@polarbiasAgeGenderAjax');

#----------- Polarbais By Option School FIlter --------
            Route::get('optional-school-filter', 'Staff\Astracking\Analytics\Optional_filter_controller@polarbiasOptFilter');
            Route::post('optional-school-filter-ajax', 'Staff\Astracking\Analytics\Optional_filter_controller@polarbiasOptFilterAjax');

#----------- Mean By School Type -----------
            Route::get('mean-by-school-type', 'Staff\Astracking\Analytics\Mean_school_type_controller@meanBySchoolType');
            Route::post('mean-by-school-type-ajax', 'Staff\Astracking\Analytics\Mean_school_type_controller@meanBySchoolTypeAjax');

#----------- Polarbias Gen Con ------------
            Route::get('polarbias-gen-con', 'Staff\Astracking\Analytics\Polarbias_gen_con_controller@polarbiasGenCon');
            Route::post('polarbias-gen-con-ajax', 'Staff\Astracking\Analytics\Polarbias_gen_con_controller@polarbiasGenConAjax');

#----------- your school composite risks vs national ------------
            Route::get('composite-risk-comparision', 'Staff\Astracking\Analytics\Composite_comparision_controller@compositeComparision');
            Route::post('composite-risk-comparision-ajax', 'Staff\Astracking\Analytics\Composite_comparision_controller@compositeComparisionAjax');

#----------- YOUR SCHOOL ADOLESCENT TRENDS VS NATIONAL -------------
            Route::get('development-trend-comparision', 'Staff\Astracking\Analytics\Development_trend_controller@developmentTrend');
            Route::post('development-trend-ajax', 'Staff\Astracking\Analytics\Development_trend_controller@developmentTrendAjax');

#-----------IMPACT ON PP* PUPILS SINCE PREVIOUS ASSESSMENT -------------
            Route::get('impact-on-priority-pupil', 'Staff\Astracking\Analytics\ImpactOn_priority_pupil_controller@impactOnPriorityPupil');
            Route::post('impact-on-priority-pupil-ajax', 'Staff\Astracking\Analytics\ImpactOn_priority_pupil_controller@impactOnPriorityPupilAjax');

#----------- IMPACT CR PUPILS SINCE PREVIOUS ASSESSMENT ---------------
            Route::get('impact-on-composite-pupil', 'Staff\Astracking\Analytics\ImpactOn_composite_pupil_controller@ImpactOnCompositePupil');
            Route::post('impact-on-composite-pupil-ajax', 'Staff\Astracking\Analytics\ImpactOn_composite_pupil_controller@ImpactOnCompositePupilAjax');

#------------ TRACKING % OF PP* IN A SPECIFIC PUPIL COHORT YEAR ON YEAR ---------
            Route::get('tracking-pp-cohort', 'Staff\Astracking\Analytics\Tracking_pp_cohort_controller@trackingPPCohort');
            Route::post('tracking-pp-cohor-ajax', 'Staff\Astracking\Analytics\Tracking_pp_cohort_controller@trackingPPCohortAjax');

#------------ IMPACT OF PUPIL ACTION PLANS SINCE PREVIOUS ASSESSMENT -----------
            Route::get('actionplan-consultant', 'Staff\Astracking\Analytics\Actionplan_consultant_controller@actionplanConsultant');

#----------- TRACKING FACTOR MEAN FOR A YEAR/HOUSE YEAR ON YEAR -------------
            Route::get('tracking-factor-mean', 'Staff\Astracking\Analytics\Tracking_factor_mean_controller@trackingFactorMean');
            Route::post('tracking-factor-mean-ajax', 'Staff\Astracking\Analytics\Tracking_factor_mean_controller@trackingFactorMeanAjax');

            # ---------------- Senior-Practitioner-Contract  ------------------
            Route::get('senior-practitioner-contract', 'Staff\Astracking\Platform_ast_controller_staff@seniorPractitionerContract');
            Route::get('contract-media', 'Staff\Astracking\Platform_ast_controller_staff@openContractMedia');

            # ---------------- School-Dates ------------------
            Route::get('school-dates', 'Staff\Astracking\Platform_ast_controller_staff@schoolDates');
            Route::any('display-school-dates', 'Staff\Astracking\Platform_ast_controller_staff@getSchoolDatesData');
            Route::post('save-school-dates', 'Staff\Astracking\Platform_ast_controller_staff@saveSchoolDates');
            Route::get('export-pdf-school-dates', 'Staff\Astracking\Platform_ast_controller_staff@exportPdfSchoolDates');
            Route::get('export-ical-calender', 'Staff\Astracking\Platform_ast_controller_staff@exportIcalCalender');

#************ End Analytics *************#
//
        });
#------- L-6 -----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(consultant)'],
            'middleware' => 'level'
                ], function() {
            #----------- Access control(admin-sga) -------------
            Route::get('admin-sga', 'Staff\Astracking\Platform_ast_controller_staff@adminSga');
            Route::get('sch-proforma-overview', 'Staff\Astracking\Platform_ast_controller_staff@schProformaOverview');
            Route::post('get-admin-sga-schools', 'Staff\Astracking\Platform_ast_controller_staff@adminSgaSchools');
            Route::post('admin-super-ajax', 'Staff\Astracking\Platform_ast_controller_staff@adminSuperAjax');
            Route::post('super-ajax', 'Sga\Sga_controller@superAjax');
            Route::get('pupil-list', 'Sga\Sga_controller@pupilList');
            Route::post('get-pupil', 'Sga\Sga_controller@getPupil');
            Route::post('fetch-schools', 'Sga\Sga_controller@fetchSchools');
            Route::get('scheduled-calender', 'Sga\Scheduled_calender_controller@scheduledCalender');
        });


# ---------------- Start Footprint section route ------------------

        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {
            Route::get('platform-footprint-menu', 'Staff\Footprint\Platform_footprint_controller@platformFootprint');
            Route::get('footprint-tile/{stage?}', 'Staff\Footprint\Platform_footprint_controller@footprintTile');
            Route::post('footprint-ajax-action', 'Staff\Footprint\Platform_footprint_controller@footprintAjaxAction');
            Route::get('footprint-resources', 'Staff\Footprint\Platform_footprint_controller@footprintResources');
            Route::post('footprint-upload-files', 'Staff\Footprint\Platform_footprint_controller@footprintUploadFiles');
            Route::post('footprint-change-level', 'Staff\Footprint\Platform_footprint_controller@footprintChangeLevel');
            Route::post('footprint-change-show', 'Staff\Footprint\Platform_footprint_controller@footprintChangeShow');
            Route::post('footprint-delete-file', 'Staff\Footprint\Platform_footprint_controller@footprintDeleteFile');
            Route::post('footprint-update-schools', 'Staff\Footprint\Platform_footprint_controller@footprintUpdateSchool');
            Route::get('resource-footprints', 'Staff\Footprint\Platform_footprint_controller@openResourceFootprint');
            Route::post('update-footprint-order-list', 'Staff\Footprint\Platform_footprint_controller@updateFootprintOrderList');
        });
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(pupil)'],
            'middleware' => 'level',
                ], function () {
            Route::get('pupil-platform-footprint', 'Pupil\Footprint\Platform_footprint_controller@pupilPlatformFootprint');
        });


# ---------------- Start Cas Tracking section route ------------------
#-------L-4 L-5 and L-6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(teacher|senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {

            Route::get('platform-cas-menu', 'Staff\Castracking\Platform_cas_controller@platformCasMenu');
            Route::get('platform-cas-admin', 'Staff\Castracking\Platform_cas_controller@platformCasAdmin');
        });

    # ---------------- Start USTEER section route ------------------
    #-------L-4 L-5 and L-6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(teacher|senior-practitioner|consultant)'],
            'middleware' => 'level'
                ], function() {

            #---------------------- Training --------------------------
            Route::get('training', 'Staff\Usteer\Platform_usteer_controller@training');
            #---------------------- Animation --------------------------
            Route::get('animation', 'Staff\Usteer\Platform_usteer_controller@animation');
            #---------------------- download --------------------------
            Route::get('download', 'Staff\Usteer\Platform_usteer_controller@download');
        });

        #-------L-3 L-4 L-5 and L6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner)|consultant'],
            'middleware' => 'level'
                ], function() {

            #---------------------- Usteer Menu --------------------------
            Route::get('platform-usteer-menu', 'Staff\Usteer\Platform_usteer_controller@platformUsteerMenu');

            #---------------------- Admin --------------------------
            Route::get('platform-usteer-admin', 'Staff\Usteer\Platform_usteer_controller@platformUsteerAdmin');
            Route::get('usteer-hub', 'Staff\Usteer\Platform_usteer_controller@redirectToUsteerHub');
        });

        #-----------------------L-5 only---------------------------------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner)|consultant'],
            'middleware' => 'level'
                ], function() {
            #---------------------- app demo --------------------------
            Route::get('app-demo', 'Staff\Usteer\Platform_usteer_controller@appDemo');
        });
        #-----------------------L-3 L-5 only---------------------------------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|senior-practitioner)'],
            'middleware' => 'level'
                ], function() {
            #---------------------- app demo --------------------------
            Route::get('usteer-tutors', 'Staff\Usteer\Platform_usteer_controller@usteerTutors');
            Route::post('ajax-usteer-staff-upload', 'Staff\Usteer\Platform_usteer_controller@ajaxUsteerStaffUpload');
        });

    });

    #---------------------- Common Group(astracking, castracking, usteer) --------------------------
    Route::group([
        'prefix' => '{package}',
        'where' => ['package' => '(astracking|castracking|usteer)'],
        'middleware' => 'package'
            ], function() {
        #-------L-3 L-4 L-5 and L6 Only-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(it-staff|teacher|senior-practitioner)|consultant'],
            'middleware' => 'level'
                ], function() {

            # ---------------- Resources  ------------------
            Route::get('resources', 'Staff\Common\Resources\Resource_controller@resources');
            Route::get('resources-tile/{tile}', 'Staff\Common\Resources\Resource_controller@resourceTile');
            Route::get('resource-column-update/{id}/{set}/{value}/', 'Staff\Common\Resources\Resource_controller@resourceColumnUpdate');
            Route::get('resource-column-delete/{id}', 'Staff\Common\Resources\Resource_controller@resourceColumnDelete');
            Route::post('resource-school-edit', 'Staff\Common\Resources\Resource_controller@resourceSchoolEdit');
            Route::get('resource-media', 'Staff\Common\Resources\Resource_controller@openResourceMedia');
        });
        #-------L-6 L-7-----------
        Route::group([
            'prefix' => '{level}',
            'where' => ['level' => '(consultant|admin)'],
            'middleware' => 'level'
                ], function() {
# ---------------- Resources  ------------------
            Route::get('resource-file-upload/{platform}', 'Staff\Common\Resources\Resource_controller@resourcesUpload');
            Route::post('file-upload', 'Staff\Common\Resources\Resource_controller@fileUpload');
        });
    });
});
