<?php

use App\Models\Dbglobal\Model_dat_package_subscription;
use App\Models\Dbschools\Model_ass_main;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_report_actionplan;
use App\Models\Dbschools\Model_report_family_signpost;
use App\Services\ActionPlanMetaServiceProvider;
use Illuminate\Support\Arr;
use \DateTime as DateTime;
use App\Services\ContentAssessmentProvider;
use App\Models\Dbglobal\Model_dat_schools;
use App\Models\Dbschools\Model_arr_year;
use App\Services\AstNextServiceProvider;
use App\Util\Builder\Safeguarding\SafeguardingBuilder;
use phpDocumentor\Reflection\Types\Nullable;
use App\Services\CohortServiceProvider;
use Illuminate\Support\Facades\Session;
use App\Models\Dbschools\Model_new_permission;
use App\Util\Grouping\Composite\Composite;
use App\Util\Grouping\PolarRisk\PolarRisk;
use App\Models\Dbportal\Model_dat_school_review;
use App\Models\Dbportal\Model_str_school_overview;
use App\Models\Dbschools\Model_multischools;
use App\Models\Dbportal\Model_sga_auth;
use App\Models\Dbportal\Model_dat_school_tracking;
use App\Models\Dbschools\Model_portal_school_info;
use App\Models\Dbschools\Model_arr_subschools;
use App\Models\Dbglobal\Model_rollover_communication;
use App\Models\Dbglobal\Model_dat_school_list;
use Illuminate\Support\Str;

function IsDataAvailableInYear($school_id)
{
    $actionPlanMeta = new ActionPlanMetaServiceProvider();
    $assMainData = new Model_ass_main();
    $yearList = $actionPlanMeta->academicYearsList($school_id);
    foreach (array_reverse($yearList) as $year) {
        $data = $assMainData->CheckIfDataIsAvailable($year);
        if (count($data) > 0)
            return $year;
    }
    $yearList = array_reverse($yearList);
    return $yearList[0] ?? null;
}

function getStudentDataAvailableInYear( $school_id, $pupil_id, $filter, $current_year, $assessment_type )
{
    $actionPlanMeta = new ActionPlanMetaServiceProvider();
    $assMainData = new Model_ass_main();
    $yearList = $actionPlanMeta->academicYearsList($school_id);
    $data = [];
    $rounds = [];
    for ( $i = 1; $i <= 3; $i++ ) {
        if( $filter['round'][0] != $i )
            $rounds[$current_year][] = $i;
    }
    foreach (array_reverse($yearList) as $year) {
        if( $current_year >= $year) {
            $filter['academic_year'][0] = $year;
            $filter['round'] = $current_year == $year ? $rounds[ $current_year ] : [ 3,2,1 ];
            $data[$year] = $assMainData->getStudentDataAvailableInYear( $filter, $pupil_id, $assessment_type );
        }
    }
    return $data;
}

function IsDataAvailableInYearParticularPupil($year, $student_id, $round) {
    $assMainData = new Model_ass_main();
    $data = $assMainData->CheckIfDataIsAvailableForParticularPupil($year, $student_id, $round);
    if($data)
        return array('year' => $year, 'data' => $data);
    return null;
}

function IsDataAvailableInYearStudent($school_id, $pupil_id)
{
    $actionPlanMeta = new ActionPlanMetaServiceProvider();
    $assMainData = new Model_ass_main();
    $yearList = $actionPlanMeta->academicYearsList($school_id);
    foreach (array_reverse($yearList) as $year) {
        $data = $assMainData->CheckIfDataIsAvailableStudent($year, $pupil_id);
        if (count($data) > 0)
            return $year;
    }
    return null;
}

function RoundLatest($year)
{
    $ass_main = new Model_ass_main();
    $round =  $ass_main->round($year);

    if( $round == null )
        return 1;
    else
        return $round;
}

function Studentlatest($year, $round, $pupil_id )
{
    $ass_main = new Model_ass_main();
    return $ass_main->studentlatest( $year, $round, $pupil_id );
}


function DynamicRound($year)
{
    $ass_main = Model_ass_main::year($year)->where('is_completed', 'Y')->where('completed_date', '!=', '')->orderBy('id', 'desc')->first();
    $round = 0;
    if ($ass_main) {
        $time = time();
        $lastTime = strtotime($ass_main->completed_date);
        $diff = ($time - $lastTime) / 86400;
        $round = $diff >= 90 ? $ass_main->round++ : $ass_main->round;
    } else {
        $round = 1;
    }
    return $round <= 0 ? 1 : $round;
}

function academicYearsList($school_id)
{
    $data = [];
    $astNext = new AstNextServiceProvider();
    $tables = $astNext->schoolTables($school_id);

    foreach ($tables as $table) {
        if (str_contains($table, "ass_score")) {
            $explodeYears = explode("_", $table);
            if(count($explodeYears) == 3){
                if(strlen($explodeYears[2]) == 4){
                    $data[] = (int)$explodeYears[2];
                }
            }
        }
    }
    return $data;
}



function FilterActionPlanRisk($risks, $actionPlans, $type)
{
    $actionPlans = Arr::flatten($actionPlans);
    $polar_bias = $risks['risks'];
    $composite_bias = $risks['composite_risks'];
    $filteredComposite = [];
    $filteredPolarBias = [];
    foreach ($polar_bias as $k=>$polarbias) {
        $bias = $type == 'MONITOR_COMMENT' ? $polarbias['type'] : GetBiasInAbbrev($polarbias['type']);
        if (!in_array($bias, $actionPlans)) {
            $filteredPolarBias[] = $polarbias;
        }else{
            $polarbias['checked'] = true;
            $filteredPolarBias[] = $polarbias;
        }
    }
    foreach ($composite_bias as $compositebias) {
        $bias = $type == 'MONITOR_COMMENT' ? $compositebias['type'] : GetBiasInAbbrev($compositebias['type']);
        if (!in_array($bias, $actionPlans)) {
            $filteredComposite[] = $compositebias;
        }else{
            $compositebias['checked'] = true;
            $filteredComposite[] = $compositebias;
        }
    }
    return array(
        'polar_biases' => $filteredPolarBias,
        'composite_risks' => $filteredComposite
    );
}



function ActionPlanObjectFormat($value, $title_filter, $riskType, $names)
{
    $value = (object)$value;
    $value->date_created = $value->date_time;
    $value->bias = $value->type_banc;
    $value->title_filter = $title_filter;
    $value->riskType = $riskType;
    $value->student_id = $value->pop_id;
    $value->names = $names;
    return $value;
}

function prepareStatement($value, $bias)
{
    $name = str_replace('-', ' ', BiasName($bias));
    $name = str_replace('_', ' ', $name);
    $statement = '';
    if ($value >= 0 && $value <= 3)
        $statement = "I have polar low $name";
    else if ($value >= 3.75 &&  $value <= 4.5)
        $statement = "I have low $name";
    else if ($value >= 5.25 &&  $value <= 6.75)
        $statement = "I have slightly lower $name";
    else if ($value == 7.5)
        $statement = "I have equal $name";
    else if ($value >= 8.25 && $value <= 9.75)
        $statement = "I have slightly higher $name";
    else if ($value >= 10.5 && $value <= 11.25)
        $statement = "I have high $name";
    else if ($value >= 12)
        $statement = "I have polar high $name";

    return $statement;
}

function feel($bias, $value)
{
    if ($bias == 'SELF_DISCLOSURE') {
        if ($value >= 0 && $value <= 4.5) {
            $feel = "I like to keep my thoughts, ideas, opinions and feelings to myself; it doesn't mean that I'm shy or introverted - I just like my privacy. I might be really chatty and spend time hanging out with others, but I'm unlikely to  tell or show what is really going on for me. I prefer time alone in my own world, working independently and thinking through things on my own, rather than with others. If I was struggling, you probably wouldn't know because I'm unlikely to tell or show that anything is wrong.";
        } else if ($value >= 5.25 &&  $value <= 9.75) {
            $feel = "I'm good at deciding what to share with others - like telling my best friend that I'm worried. I'm good at deciding to keep something private - like waiting till my teacher has finished talking before I ask my question. It depends on where I am, what I'm doing and who I'm with!  I know that privacy is important and think carefully about what I share and with whom. I like time with other people and talking things through, but I also need time on my own for thinking independently. If I was struggling, I'd think carefully about who to talk to, what to say, and when to say it.";
        } else if ($value >= 10.5 && $value <= 15) {
            $feel = "I like to share my thoughts, ideas, opinions and feelings with lots of people; But, I don't always make wise decisions about what I share with whom or whether this is the right time to say it. I don't always stop and think about the consequences. I prefer hanging out with other people, talking things through and being involved in what's going on; I don't need much privacy or solitude. If I was struggling you'd probably know; the problem is I might not always reach out to the right place or person for support.";
        }
    } else if ($bias == 'TRUST_OF_SELF') {
        if ($value >= 0 && $value <= 4.5) {
            $feel = "I often question myself - what I think, say or do; what I look like; what my abilities are. I doubt if anyone will notice or value me and can easily feel overlooked or dismissed. I usually notice what's happening around me though and can be overly sensitive or empathic at times. I'm easily affected by what other people do or say and can react strongly to feedback. If I was struggling, I would doubt if I had the skills, qualities and resources to overcome these challenges.";
        } else if ($value >= 5.25 &&  $value <= 9.75) {
            $feel = "I'm good at thinking about when to trust myself - like when I'm doing something familiar. I also know when to question myself - like asking my teacher to check I've got the right answer. It depends on what I'm doing and who I'm with! I like to be noticed and valued for what I do or say, but I don't need this all the time! I notice what is happening around me, but can shut off to it when I need to. I can be affected by what people think, say or do and take feedback onboard, but I'm careful about who I let influence me. If I was struggling, I'd feel quite confident that I had the skills, qualities and resources to overcome these challenges.";
        } else if ($value >= 10.5 && $value <= 15) {
            $feel = "I often trust myself - what I think, say or do;  what I look like; what my abilities are. I assume that other people will notice or value me - and don't expect to be overlooked or dismissed. I don't really notice what's happening around me though and can be a bit insensitive or indifferent at times. I'm not often affected by what other people think, say, or do and tend to dismiss or shrug off feedback. If I was struggling, I'd assume I had the skills, qualities and resources to overcome these challenges.";
        }
    } else if ($bias == 'TRUST_OF_OTHERS') {
        if ($value >= 0 && $value <= 4.5) {
            $feel = "I'm cautious about how much I trust people; I doubt whether other people can or want to help me and whether they are reliable. I'd rather rely on myself  in case others let me down. I'll be sceptical about other people's ideas and opinions and won't be interested in following the crowd; it's safer to do things on my own terms.  If I was struggling, I 'd doubt others would or could help me; I'd rely on myself to overcome these challenges.";
        } else if ($value >= 5.25 &&  $value <= 9.75) {
            $feel = "I'm good at deciding when to trust other people and when to be more cautious. I'm more likely to  trust someone I've  known for a while, than someone I've only just met. I think about how much I can expect of others and whether I can or should ask them for support. I listen carefully when people ask me to do things and weigh up whether it's a good idea or not. If I was struggling, I'd think about who I could trust to help me as well as what I could do to help myself.";
        } else if ($value >= 10.5 && $value <= 15) {
            $feel = "I give people the benefit of the doubt. I assume that people will want to help and support me and that most people are trustworthy and reliable.  I'd rather rely on other people than myself, it's easier and they'll probably do it better than me. I usually do what I'm asked, accept what people say and follow the crowd; I don't like being left out. If I was struggling, I'd assume that others would help me, rather than draw on my own resources to overcome these challenges.";
        }
    } else if ($bias == 'SEEKING_CHANGE') {
        if ($value >= 0 && $value <= 4.5) {
            $feel = "I like things to stay the same. I like to know what's going to happen and when. I prefer doing what I'm already good at, because getting things right and doing things well is important to me. I'm happy with the same familiar friends, rather than making new ones. You see I don't like surprises or risks! I like to feel in control. If I was struggling, I'd find way to feel more in control again which may lead to some unhealthy coping strategies which may be hidden.";
        } else if ($value >= 5.25 &&  $value <= 9.75) {
            $feel = "I'm good at deciding how much change is good for me. Though change can be exciting and energising, I also know that familiar and predictable things make me feel safe so I like a bit of both! I like to know what's going to happen, but surprises don't worry me. I push myself out of my comfort zone to try new things, but I know when something is too risky or too much for me. Whilst I enjoy meeting new people, I've got a close group of friends who stick together through all the ups and downs. If I was struggling, I'd ask myself if I need to persevere with it or move on.";
        } else if ($value >= 10.5 && $value <= 15) {
            $feel = "I like things to change. I get bored if things stay the same. I like trying new things because having a go and exploring is what matters to me. I'd rather do something quick than do it right! It's exciting meeting new people; it can be a bit dull hanging out with the same people. You see I like surprises and risks! If I was struggling, I'd find a way to take my mind off it, which may lead to some unhealthy coping strategies which may be hidden.";
        }
    } else if ($bias == 'OVER_REGULATION') {
        $feel = "I'm always thinking about what choice to make in different situations and interactions. You might even praise me for doing this so skillfully. But what you don't know, is how exhausting it is to constantly monitor how to be or what to do. You may not know why I do this, or what risks I face if I stop doing it? You might be suprised when suddenly, it it all gets too much and I struggle to cope.";
    } else if ($bias == 'HIDDEN_VULNERABILITY') {
        $feel = "I feel vulnerable. But you probably won't know that because I find ways to hide it from you.  If I was struggling, I'd doubt whether you could help me, and I'd probably push you away if you tried. I  doubt whether I could find a way through my sturggle. I could feel very alone and defeated.";
    } else if ($bias == 'SOCIAL_NAIVETY') {
        $feel = "I feel optimistic because I assume everything will be okay; why wouldn't it be! I like to have a go at new things, but don't always stop to think if that's a good idea.  I avoid struggle by ignoring it or moving on to something else.";
    } else if ($bias == 'HIDDEN_AUTONOMY') {
        $feel = "I feel  assured and in control.  I do things my own way, on my own terms, without relying on anyone else. And I  only do things that I know I can do;  I'm no risk-taker. I like a lot or privacy, though you may not know that because I tell you what I want you to know.  If I was struggling; I'd sort it out on my own, without reaching out to anyone.";
    } else if ($bias == 'SEEKING_CHANGE_INSTABILITY') {
        $feel = "Child 1 I feel under a lot of pressure.  I'm managing lot of change at school just now.  Out of school,  I need to feel in control, safe and secure. Child 2 I feel under a lot of pressure.  I'm so controlled and focused at school. Out of school, I want to  switch off, explore, take risks, and  escape.";
    } else {
        $feel = $bias;
    }
    return $feel;
}

function TimeUntilDeletion($date, $type)
{
    if ($type == 'INCOMPLETE') {
        $date = date_create($date);
        date_add($date, date_interval_create_from_date_string('72 hours'));
        return date_format($date, 'Y-m-d H:i:s');
    }
    return null;
}

function SpeedInformation($speed)
{
    if ($speed == '')
        return '';
    else if ($speed == 'run')
        return 'COMPLETED QUICKLY';
    else if ($speed == 'walk')
        return 'COMPLETED SLOWLY';
    else
        return '';
}

function validateDateFormat($date,$format='Y-m-d H:i:s')
{
	$dt = DateTime::createFromFormat($format, $date);
	return $dt !== false && !array_sum($dt->getLastErrors());
}

function CalculateTime($rawdata, $num, $type)
{
    if (!isset($rawdata->trackingdata->qtrack))
        return null;

    $qtrack = $rawdata->trackingdata->qtrack;
    $start_time = $end_time = null;
    $start_time = $rawdata->trackingdata->start;
    if ($num == 1) {
        //$start_time = $rawdata->trackingdata->start;
        $end_time = isset($qtrack[0]) ? $qtrack[0] : null;
    } else if ($num < 16) {
        //$start_time = isset($qtrack[$num - 2]) ? $qtrack[$num - 2] : null;
        $end_time = isset($qtrack[$num - 1]) ? $qtrack[$num - 1] : null;
    } else if ($num == 16) {
        //$start_time = isset($qtrack[$num - 2]) ? $qtrack[$num - 2] : null;
        $end_time = isset($rawdata->trackingdata->end) ? $rawdata->trackingdata->end : null;
    }
    return CustomTimeTaken($start_time, $end_time, $type);
}

function TimeTaken($started_date, $completed_date, $type)
{
    if ($type == 'NOT_STARTED' || $type == 'INCOMPLETE')
        return null;

    if( $completed_date == '0000-00-00 00:00:00' )
        return null;

    $completed_date = strtotime($completed_date);
    $started_date = strtotime($started_date);
    $diff = $completed_date - $started_date;
    return gmdate("H:i:s", $diff );
}

function CustomTimeTaken($started_date, $completed_date, $type)
{
    if ($type == 'NOT_STARTED')
        return null;
    if( $completed_date == '0000-00-00 00:00:00' )
        return null;

    $completed_date = strtotime($completed_date);
    $started_date = strtotime($started_date);
    $diff = $completed_date - $started_date;
    $start_time = date('H:i:s', $started_date );
    $diff_time = gmdate('i:s', $diff );
    return $diff_time;
}

function getQuestion($i, $questions)
{
    return $questions[$i - 1] ?? null;
}

function getAnswer($i, $score, $answers)
{
    return $answers[$i - 1][$score - 1] ?? null;
}

function CompletionSpeed($student_data, $speed)
{
    if ($speed == "")
        return null;
    $contentAssessmentProvider = new ContentAssessmentProvider();
    $text = $contentAssessmentProvider->completionSpeed($student_data, $speed);
    if ($speed == 'COMPLETED QUICKLY')
        $type = 'QUICKLY';
    else if ($speed == 'COMPLETED SLOWLY')
        $type = 'SLOWLY';
    else
        $type = "";
    return array(
        'text' => $text,
        'label' => ucfirst($speed),
        'type' => $type
    );
}

function CheckValidityStudentActionPlan($request, $id)
{
    $actionPlan = new Model_report_actionplan();
    $pop = new Model_population();
    $actionPlan = $actionPlan->getSingleActionPlan($id);
    if (!$actionPlan instanceof \App\Models\Dbschools\Model_report_actionplan)
        abort(404, 'Action Plan not Found');
    $user = $pop->get($request->user()->id);
    if (!$user instanceof \App\Models\Dbschools\Model_population)
        abort(404, 'User not Found');
    return true;
}

function CheckValidityFamilySignPost($request, $id)
{
    $report_signpost = new Model_report_family_signpost();
    $pop = new Model_population();
    $actionPlan = $report_signpost->getfamilySignPost($id);
    if (!$actionPlan instanceof \App\Models\Dbschools\Model_report_family_signpost)
        abort(404, 'Action Plan not Found');
    $user = $pop->get($request->user()->id);
    if (!$user instanceof \App\Models\Dbschools\Model_population)
        abort(404, 'User not Found');
    return true;
}

function UpdateStatement($statements, $request)
{
    if (!isset($statements['section_3']))
        abort(404, 'Information not Found');
    $section_1 = (array)$statements['section_1'];
    $section_2 = (array)$statements['section_2'];
    $section_3 = (array)$statements['section_3'];
    $section_3_keys =  array_keys($section_3);
    foreach ($section_3_keys as $key) {
        $arr_section_3_key = (array)$section_3[$key];
        $inner_keys = array_keys($arr_section_3_key);
        foreach ($inner_keys as $ikey) {
            (array)$section_3[$key][$ikey]['c4'] = $request->impact;
        }
    }

    $sections = array(
        'section_1' => $section_1,
        'section_2' => $section_2,
        'section_3' => $section_3
    );
    return json_encode($sections, true);
}

function Description($score, $bias)
{
    $value = isset($score['score']) ?  (float)$score['score'] : (float)$score;
    $statement = prepareStatement($value, $bias);
    $feel = feel($bias, $value);
    return array(
        'statement' => isset($statement) ? $statement : '',
        'feel' => isset($feel) ? $feel : ''
    );
}

function staticnameandlinks($school_id,$request)
{
    $biases_infos = fetchbiasesdata();
    $Model_dat_schools = new Model_dat_schools();
    $detail = $Model_dat_schools->SchoolName($school_id);
    $result = $detail->name;
    foreach($biases_infos->factor_biases as $keys=>$biases){
        foreach($biases->measures as $key=>$bias){
            $bias->measure = str_replace('##school##', $result, $bias->measure);
            $bias->measure = str_replace('##school house/ school##', $result, $bias->measure);
        }
    }
    return response()->json(
        $biases_infos
    , 200);
}

function AstNextUserType($user)
{
    if ($user->level == '1') {
        return 'Student';
    } else if ($user->level == '4') {
        return 'Teacher';
    } else if ($user->level == '5') {
        return 'Senior Practitioner';
    } else if ($user->level == '6') {
        return 'Consultant';
    } else if ($user->level == '7') {
        return 'Admin';
    }
}

function AstNextUserLevel($user)
{
    return $user->level;
}


function CompositeAbbrev($score)
{
    $data = [];
    if ($score['risk_sn'] == '1')
        $data['social_naivety'] = 'Social Naivety';
    if ($score['risk_hv'] == '1')
        $data['hidden_vulnerability'] = 'Hidden Vulnerability';
    if ($score['risk_ha'] == '1')
        $data['hidden_autonomy'] = 'Hidden Autonomy';
    if ($score['or_risk'] == '1')
        $data['over_regulation'] = 'Over Regulation';
    if ($score['risk_sci'] == '1')
        $data['seeking_change_instability'] = 'Seeking Change Instability';
    return $data;
}

function SchoolInfoType($school_info_type)
{
    if ($school_info_type == 'IN_SCHOOL')
        return 'In School';
    else
        return 'Out Of School';
}

function historyFilterByRound( $filter, $type ) {
    $assmain = new Model_ass_main();
    foreach( $filter['academic_year'] as $year ) {
        foreach( [3,2,1] as $round ) {
            $tempfilter['academic_year'][0] = $year;
            $tempfilter['round'][0] = $round;
            $assessment_list = $assmain->getAssessmentReport($tempfilter, $type );
            $filter['historyfilter'][] = [ 'academic_year' => $year, 'round' => $round, 'assessment_list' => $assessment_list ];
        }
    }
    return $filter;
}

function setDefaultFilter($filter, $school_id)
{
    if (!isset($filter['academic_year']))
        $filter['academic_year'][] = IsDataAvailableInYear($school_id);
    if (!isset($filter['round']))
        $filter['round'][] = 1;

    return $filter;
}

function Metadata($request)
{
    $page = $request->has('page') ? $request->get('page') : 1;
    $size = $request->has('size') ? $request->get('size') : 2;
    return array('page' => (int)$page, 'size' => (int)$size);
}

function AssessmentType()
{
    return [
        'IN_SCHOOL' => ['sch', 'hs'],
        'OUT_OF_SCHOOL' => ['at']
    ];
}

function renameStudentScoreColumn($score)
{
    if( $score == null )
        return null;

    $score = (object)$score;
    if( !isset($score->datetime) )
        null;

    $date = date_create($score->datetime);
    return [
        'SELF_DISCLOSURE' => (float)$score->P, 'TRUST_OF_SELF' => (float)$score->S,
        'TRUST_OF_OTHERS' => (float)$score->L, 'SEEKING_CHANGE' => (float)$score->X,
        'date' => date_format($date, 'Y-m-d')
    ];
}

function getYearFromDate($score)
{
    $date = date_create($score->datetime);
    return date_format($date, 'Y');
}

function getAssessmentDate($score)
{
    $date = date_create($score->datetime);
    return date_format($date, 'Y-m-d');
}

function buildFetchParamForPastAssessment2($current_round, $current_academic_year, $school_id, $pupil_id )
{
    if ($current_round == 1) {
        $yearList = academicYearsList($school_id);
        $academic_year = $current_academic_year - 1;
        $round = 3;
        if (!in_array($academic_year, $yearList))
            return null;

        //$latest_assessment = Studentlatest($academic_year, 3, $pupil_id);
        // $round = isset( $latest_assessment->round ) ? $latest_assessment->round : null;
        // if( $round == null ) {
        //     buildFetchParamForPastAssessment2( ($current_round - 1), $current_academic_year, $school_id, $pupil_id )
        // }
        //return null;
    } else {
        $round = $current_round - 1;
        $academic_year = $current_academic_year;
    }
    $filter['academic_year'][0] = $academic_year;
    $filter['round'][0] = $round;
    return $filter;
}

function Packages($school_id)
{
    $dat_school_model = new Model_dat_schools();
    $school_detail = $dat_school_model->SchoolDetail($school_id);
    $prduct_arr = $school_detail['products'];
    $ints = array_map('intval', explode(',', $prduct_arr ));
    $permissions = [];
    if(in_array(1,$ints)){
        $permissions[] = 'tracking';
    }
    if(in_array(2,$ints)){
        $permissions[] = 'safeguarding';
    }
    if(in_array(3,$ints)){
        $permissions[] = 'impact';
    }
    if(in_array(4,$ints)){
        $permissions[] = 'pshe';
    }
    if(in_array(5,$ints)){
        $permissions[] = 'soft_skills';
    }
    return $permissions;
}

function CheckPackage($school_id, $value)
{
    $packages = Packages($school_id);
    if (in_array($value, $packages))
        return true;
    abort(400, 'Access Denied no Subscription found to Package');
}

function getAssessmentDataByType($school_data,  $ass_main_id)
{
    foreach ($school_data as $data) {
        if ($data->ass_main_id == $ass_main_id)
            return $data;
    }
    return null;
}
function getAssessmentRawData($school_data,  $ass_main_id)
{
    $data = getAssessmentDataByType($school_data,  $ass_main_id);
    if ($data == null)
        return null;
    return RawDataArray($data);
}

function RawDataArray($raw)
{
    if( $raw == null )
        return null;
    for ($i = 1; $i <= 16; $i++) {
        if ($i < 10)
            $array_data[] = $raw->{"q0" . $i};
        else if ($i <= 16)
            $array_data[] = $raw->{"q" . $i};
    }

    return implode(',', $array_data);
}

function FlagMessage($count)
{
    if ($count == 1)
        return 'Flagged only once';
    else
        return 'Flagged ' . $count . 'x In a row';
}

function safeguardingDescription($label)
{
    $safeguardingBuilder = new SafeguardingBuilder();
    $info = [];
    if ( strtolower($label) == 'polar low self disclosure') {
        $info[] = $safeguardingBuilder->description('Undisclosed, unchallenged views, which may be fed by online communities');
        $info[] = $safeguardingBuilder->description('Undisclosed, hidden online behaviours');
        $info[] = $safeguardingBuilder->description('Mask and deflect vulnerabilities  and risks');
    } else if ( strtolower($label) == 'polar high self disclosure') {
        $info[] = $safeguardingBuilder->description('Impetuous,  impulsive behaviours');
        $info[] = $safeguardingBuilder->description('Unfiltered, naive online behaviours e.g. sexting');
        $info[] = $safeguardingBuilder->description('Share intimate concerns in unsafe contexts');
    } else if ( strtolower($label) == 'polar low trust of self') {
        $info[] = $safeguardingBuilder->description('Highly impressionable, adopt views and behaviours of others e.g. gangs');
        $info[] = $safeguardingBuilder->description('Drawn into coercive relationships; risks of grooming');
        $info[] = $safeguardingBuilder->description('Easily defeated, very low self-efficacy');
    } else if ( strtolower($label) == 'polar high trust of self') {
        $info[] = $safeguardingBuilder->description('Influential, coerce others into malign behaviours');
        $info[] = $safeguardingBuilder->description('Dominating and indifferent to others e.g. bullying behaviours');
        $info[] = $safeguardingBuilder->description('Closed to support, sense of invincibility');
    } else if ( strtolower($label) == 'polar low trust of others') {
        $info[] = $safeguardingBuilder->description('Self-reliant, trying to cope alone e.g. as a carer or victim of abuse');
        $info[] = $safeguardingBuilder->description('Seeing others as a threat, becoming cynical and isolated');
        $info[] = $safeguardingBuilder->description("Dismissive of others' views e.g. religious, ethnic, gender");
    } else if ( strtolower($label) == 'polar high trust of others') {
        $info[] = $safeguardingBuilder->description('Easily manipulated. Risk of grooming, gang exploitation');
        $info[] = $safeguardingBuilder->description('Highly impressionable, adopt views and behaviours of others e.g. radicalisation');
        $info[] = $safeguardingBuilder->description("People-pleasing behaviours, drawn into coercive relationships");
    } else if ( strtolower($label) == 'polar low seeking change') {
        $info[] = $safeguardingBuilder->description('Risk aversion and inhibition e.g. school refusal');
        $info[] = $safeguardingBuilder->description('Mental health risks e.g. hyperfocus, rumination, over checking and fixation');
        $info[] = $safeguardingBuilder->description("Controlling coping strategies likely to be hidden. E.g. restricted eating, self-harm, obsessions, compulsions");
    } else if ( strtolower($label) == 'polar high seeking change') {
        $info[] = $safeguardingBuilder->description('Risk-taking, exploration, and experimentation e.g. substance abuse, sexualised behaviours, online searches');
        $info[] = $safeguardingBuilder->description('Mental health risks e.g. stress, burn out');
        $info[] = $safeguardingBuilder->description("Deflective coping strategies which may be hidden. E.g. addictive gaming, substance abuse, self-harm, escapist thinking");
    } else if ( strtolower($label) == 'social naivety') {
        $info[] = $safeguardingBuilder->description('Perceived invincibility, unaware of limits and constraints');
        $info[] = $safeguardingBuilder->description('Unwise risk taking, not foreseeing consequences');
        $info[] = $safeguardingBuilder->description("Social influence, entitlement, using others as a commodity");
    } else if ( strtolower($label) == 'out of school over regulation') {
        $info[] = $safeguardingBuilder->description('Conscientious risks: perfectionism; over thinking; stress indicators; exhaustion; burn out');
        $info[] = $safeguardingBuilder->description('ASD risks: Social camouflage and mimicry hiding social communications difficulties');
        $info[] = $safeguardingBuilder->description("Social manipulation risks: Social chameleon;  manipulating others without detection");
        $info[] = $safeguardingBuilder->description("Hypervigilant students: hidden victim of abuse, bullying, coercion or inappropriate levels of responsibility for others e.g. carer");
    } else if ( strtolower($label) == 'in school over regulation') {
        $info[] = $safeguardingBuilder->description('Conscientious risks: perfectionism; over thinking; stress indicators; exhaustion; burn out');
        $info[] = $safeguardingBuilder->description('ASD risks: Social camouflage and mimicry hiding social communications difficulties');
        $info[] = $safeguardingBuilder->description("Social manipulation risks: Social chameleon;  manipulating others without detection");
        $info[] = $safeguardingBuilder->description("Hypervigilant students: hidden victim of abuse, bullying, coercion or inappropriate levels of responsibility for others e.g. carer");
    } else if ( strtolower($label) == 'hidden autonomy') {
        $info[] = $safeguardingBuilder->description('Few intimate relationships, emotionally isolated');
        $info[] = $safeguardingBuilder->description('Hidden controlling coping strategies e.g. restricted eating, self-harm, obsessions, rumination, avoidance');
        $info[] = $safeguardingBuilder->description("Fixed, rigid, limiting views e.g. religious, political, gender");
    } else if ( strtolower($label) == 'over regulation') {
        $info[] = $safeguardingBuilder->description('Conscientious risks: perfectionism; over thinking; stress indicators; exhaustion; burn out');
        $info[] = $safeguardingBuilder->description('ASD risks: Social camouflage and mimicry hiding social communications difficulties');
        $info[] = $safeguardingBuilder->description("Social manipulation risks: Social chameleon;  manipulating others without detection");
        $info[] = $safeguardingBuilder->description("Hypervigilant students: hidden victim of abuse, bullying, coercion or inappropriate levels of responsibility for others e.g. carer");
    } else if ( strtolower($label) == 'hidden vulnerability') {
        $info[] = $safeguardingBuilder->description('Masked vulnerability. Hidden victim of bullying, abuse, coercion');
        $info[] = $safeguardingBuilder->description('Deflective strategies: humour, avoidance, refusal, compliance, control, competence');
        $info[] = $safeguardingBuilder->description("Lack of efficacy, defeated  and stuck");
    } else if ( strtolower($label) == 'seeking change instability') {
        $info[] = $safeguardingBuilder->description('Erratic behaviours oscillating between extreme control or impulsivity');
        $info[] = $safeguardingBuilder->description('Controlling coping strategies likely to be hidden. E.g. restricted eating, self-harm, obsessions, rumination');
        $info[] = $safeguardingBuilder->description("Deflective coping strategies which may be hidden. E.g. addictive gaming, substance abuse, self-harm, escapist thinking");
    }
    return $info;
}

function FlagInformation($arr, $name, $risk, $assessment_count)
{
    $num = allflags( $arr );
    return '' . $name . ' has been flagged with ' . $risk . ' for ' . $num . ' out of '.$assessment_count.' assessment';
}

function flagInRow( $arr ) {
    $num = 0;
    foreach( $arr as $value ) {
        if($value == 1 )
            $num++;
        else
            break;
    }

    return $num;
}

function allflags( $arr ) {
    $num = 0;
    foreach( $arr as $value ) {
        if($value == 1 )
            $num++;
    }

    return $num;
}


function queryString($year)
{
    $query_string = [];
    $query_string['accyear'] = $year;
    $query_filter['academicyear'] = $year;
    $query_filter['rtype'] = '';
    $query_filter['month'] = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
    $query_filter['academicYearStart'] = '01';
    $query_filter['academicYearEnd'] = '12';
    $query_filter['academicYearClose'] = $year;
    return $query_string;
}

function conditions($year)
{
    $conditions = [];
    $conditions['year'] = $year;
    $conditions['field'] = '';
    $conditions['selected_year'] = '';
    $conditions['together_query'] = '';
    $conditions['count_query'] = '';
    $conditions['gender'] = '';
    return $conditions;
}

function GetAssessmentType($rawdata, $keyword, $type)
{
    // if($keyword == "")
    //     return $type;
    if($rawdata->is_completed == 'Y')
        return 'COMPLETED';
    else if($rawdata->is_completed == 'N')
        return 'INCOMPLETE';
    else if($rawdata->is_completed == null)
        return 'NOT_STARTED';
    // else if($rawdata->is_manipulated == '1')
    //     return 'MANIPULATED';
    // else if($rawdata->speed == 'run')
    //     return 'COMPLETED_QUICKLY';
    // else if($rawdata->speed == 'walk')
    //     return 'COMPLETED_SLOWLY';

}

function AssessmentCat($type)
{
    if($type == 'STUDENT_ACTION_PLAN')
            return ['sch', 'hs'];
        else
            return ['at'];
}

function Review( $review )
{
    if( $review == null || $review == "")
        return "Not Reviewed";
    else if( $review == "POSITIVE_IMPACT")
        return "Positive Impact";
    return "No Impact yet";
}

function ExtractAssementInfo($data, $current_assessment_list, $previous_assessment_list, $assessment_type = null )
{

    $past_student_ids = array_column( $previous_assessment_list, 'student_id' );
    $current_student_ids = array_column( $current_assessment_list, 'student_id' );

    $current_score = $previous_score = null;
    if( count($past_student_ids) == 0 || count( $current_student_ids) == 0 )
        return null;


    $past_ass_index = array_search($data->created_on, $past_student_ids);
    $current_ass_index = array_search($data->created_on, $current_student_ids);

    if($past_ass_index === false || $current_ass_index === false )
        return null;

    // process if AP date is higher than data
    if( $data->date_created < $previous_assessment_list[$past_ass_index]['completed_date'] )
        return null;


    if($data->bias == 'dl' || $data->bias == 'sdl' || $data->bias == 'dh' || $data->bias == 'sdh' || $data->bias == 'sdi') {
       $current_score = $current_assessment_list[$current_ass_index]['P'];
       $previous_score = $previous_assessment_list[$past_ass_index]['P'];
    }
    else if ( $data->bias == 'tsl' || $data->bias == 'tsh' || $data->bias == 'tsi' ) {
       $current_score = $current_assessment_list[$current_ass_index]['S'];
       $previous_score = $previous_assessment_list[$past_ass_index]['S'];
    }

    else if ( $data->bias == 'toi' || $data->bias == 'toh' || $data->bias == 'tol' ) {
       $current_score = $current_assessment_list[$current_ass_index]['L'];
       $previous_score = $previous_assessment_list[$past_ass_index]['L'];
    }

    else if ( $data->bias == 'eci' || $data->bias == 'ech' || $data->bias == 'ecl' ) {
       $current_score = $current_assessment_list[$current_ass_index]['X'];
       $previous_score = $previous_assessment_list[$past_ass_index]['X'];
    }
    else if(  $data->bias == 'ha' || $data->bias == 'hv' || $data->bias == 'blu' || $data->bias == 'or' || $data->bias == 'sn' ) {
        $record = ModifyScoreData( $current_assessment_list[$current_ass_index] );
        $rawdata = RawDataArray( $record );
        $compositeBias = changeStatementToAbbrev( $data->bias );
        $composite = new Composite();
        $list = $composite->StudentCompositeRisksObject( $record, $rawdata, $assessment_type, [] );
        $CompositeBiaslabel = array_column( $list['risks'], 'label' );
        if( !in_array($compositeBias, $CompositeBiaslabel ) ) {
            $current_score = 7.5;
            $previous_score = 0;
        }else {
            $current_score = 0;
            $previous_score = 0;
        }
    }
    else {
         $current_score = null;
         $previous_score = null;
    }



     return [ 'current_score' => $current_score, 'previous_score' => $previous_score ];
}

function ExtractAssementInfo_group_cohort($data, $current_assessment_list, $previous_assessment_list, $assessment_type = null ,$pop_id=0,$is_cohort = false)
{
    $past_student_ids = array_column( $previous_assessment_list, 'student_id' );
    $current_student_ids = array_column( $current_assessment_list, 'student_id' );
    $current_score = $previous_score = null;
    if( count($past_student_ids) == 0 || count( $current_student_ids) == 0 )
        return null;
    $past_ass_index = array_search($pop_id, $past_student_ids);
    $current_ass_index = array_search($pop_id, $current_student_ids);
    if($past_ass_index === false || $current_ass_index === false )
        return null;
    // process if AP date is higher than data
    if($is_cohort==true){
        if( $data->date_time < $previous_assessment_list[$past_ass_index]['completed_date'] )
            return null;
    }else{
        if( $data->date_created < $previous_assessment_list[$past_ass_index]['completed_date'] )
            return null;
    }

    if($data->type_banc == 'dl' || $data->type_banc == 'sdl' || $data->type_banc == 'dh' || $data->type_banc == 'sdh' || $data->type_banc == 'sdi') {
       if($is_cohort==true){
            //if($current_assessment_list[$current_ass_index]['P']>0 && $current_assessment_list[$current_ass_index]['P']<=3 || $current_assessment_list[$current_ass_index]['P']>=12){
                $current_score = $current_assessment_list[$current_ass_index]['P'];
            //}
            //if($previous_assessment_list[$past_ass_index]['P']>0 && $previous_assessment_list[$past_ass_index]['P']<=3 || $previous_assessment_list[$past_ass_index]['P']>=12){
                $previous_score = $previous_assessment_list[$past_ass_index]['P'];
           // }
       }else{
            $current_score = $current_assessment_list[$current_ass_index]['P'];
            $previous_score = $previous_assessment_list[$past_ass_index]['P'];
       }
    }
    else if ( $data->type_banc == 'tsl' || $data->type_banc == 'tsh' || $data->type_banc == 'tsi' ) {
       if($is_cohort==true){
           // if($current_assessment_list[$current_ass_index]['S']>0 && $current_assessment_list[$current_ass_index]['S']<=3 || $current_assessment_list[$current_ass_index]['S']>=12){
                $current_score = $current_assessment_list[$current_ass_index]['S'];
           // }
           // if($previous_assessment_list[$past_ass_index]['S']>0 && $previous_assessment_list[$past_ass_index]['S']<=3 || $previous_assessment_list[$past_ass_index]['S']>=12){
                $previous_score = $previous_assessment_list[$past_ass_index]['S'];
          //  }
        }else{
            $current_score = $current_assessment_list[$current_ass_index]['S'];
            $previous_score = $previous_assessment_list[$past_ass_index]['S'];
        }
    }
    else if ( $data->type_banc == 'toi' || $data->type_banc == 'toh' || $data->type_banc == 'tol' ) {
       if($is_cohort==true){
           // if($current_assessment_list[$current_ass_index]['L']>0 && $current_assessment_list[$current_ass_index]['L']<=3 || $current_assessment_list[$current_ass_index]['L']>=12){
                $current_score = $current_assessment_list[$current_ass_index]['L'];
           // }
           // if($previous_assessment_list[$past_ass_index]['L']>0 && $previous_assessment_list[$past_ass_index]['L']<=3 || $previous_assessment_list[$past_ass_index]['L']>=12){
                $previous_score = $previous_assessment_list[$past_ass_index]['L'];
          //  }
        }else{
            $current_score = $current_assessment_list[$current_ass_index]['L'];
            $previous_score = $previous_assessment_list[$past_ass_index]['L'];
        }
    }
    else if ( $data->type_banc == 'eci' || $data->type_banc == 'ech' || $data->type_banc == 'ecl' ) {
        if($is_cohort==true){
            //if($current_assessment_list[$current_ass_index]['X']>0 && $current_assessment_list[$current_ass_index]['X']<=3 || $current_assessment_list[$current_ass_index]['X']>=12 && $current_assessment_list[$current_ass_index]['X']>15 ){
                $current_score = $current_assessment_list[$current_ass_index]['X'];
           // }
           // if($previous_assessment_list[$past_ass_index]['X']>0 && $previous_assessment_list[$past_ass_index]['X']<=3 || $previous_assessment_list[$past_ass_index]['X']>=12 && $previous_assessment_list[$past_ass_index]['X']>15 ){
                $previous_score = $previous_assessment_list[$past_ass_index]['X'];
           // }
        }else{
            $current_score = $current_assessment_list[$current_ass_index]['X'];
            $previous_score = $previous_assessment_list[$past_ass_index]['X'];
        }
    }
    else if(  $data->type_banc == 'ha' || $data->type_banc == 'hv' || $data->type_banc == 'blu' || $data->type_banc == 'or' || $data->type_banc == 'sn' ) {
        $record = ModifyScoreData( $current_assessment_list[$current_ass_index] );
        $rawdata = RawDataArray( $record );
        $compositeBias = changeStatementToAbbrev( $data->type_banc );

        $composite = new Composite();
        $list = $composite->StudentCompositeRisksObject( $record, $rawdata, $assessment_type, [] );
        $CompositeBiaslabel = array_column( $list['risks'], 'label' );
        if( !in_array($compositeBias, $CompositeBiaslabel ) ) {
            $current_score = 7.5;
            $previous_score = 0;
        }else {
            $current_score = 0;
            $previous_score = 0;
        }
    }
    else {
         $current_score = null;
         $previous_score = null;
    }
    return [ 'current_score' => $current_score, 'previous_score' => $previous_score ];
}

function PluckStudentId($list)
{
    $student_ids = array_column($list, 'student_id');
    return $student_ids;
}

function PercentageCalculation($total, $count)
{
    if( $total == 0 )
        $total = 1;
    $percent = ( $count * 100 ) / $total;
    return round($percent, 1);
}

function PercentageReviewCalculation($total, $pos_review, $no_impact_review, $not_review)
{
    if( $total == 0 ) $total = 1;
    return [
        'positive_impact' => round( ( $pos_review * 100 ) / $total ),
        'no_impact_yet' => round( ( $no_impact_review * 100 ) / $total ),
        'not_reviewed' => round( ( $not_review * 100 ) / $total )
    ];
}

function PercentageCalculation2($total, $count)
{
    if( $total == 0 )
        $total = 1;
    $percent = ( $count * 100 ) / ( 4 * $total );
    return round($percent,1);
}

function PercentageCalculationByGroup( $school_data, $count_data)
{
    list(
    'male_counter' => $male_counter, 'student_counter' => $student_counter,
    'female_counter' => $female_counter, 'non_binary_counter' => $non_binary_counter,
    'house_counter' => $houses, 'year_group_counter' => $year_groups, 'all_house_count' => $all_houses,
    'total_male' => $total_male, 'total_female' => $total_female, 'total_non_binary' => $total_non_binary ) = $count_data;

}

function CountGroupData( $school_data )
{
    $gender = array_column( $school_data, 'gender');
    $counts = array_count_values($gender);

    //Male
    $male = $counts['m'] ?? 0;
    $male += $counts['male'] ?? 0;
    $gender_total['male_count'] = $male;

    //Female
    $female = $counts['f'] ?? 0;
    $female += $counts['female'] ?? 0;
    $gender_total['female_count'] = $female;

    //non binary
    $gender_total['nonbinary_count'] = count( $school_data ) - ( $gender_total['male_count'] + $gender_total['female_count'] );
    return $gender_total;
}

function CountNonBinaryGroupData( $school_data, $type )
{
    $total = array_filter($school_data, function($key) use ($school_data, $type) {
        return $school_data[$key][$type] == 'm' && $school_data[$key][$type] == 'f';
    }, ARRAY_FILTER_USE_KEY);
    return count( $total );
}

function ModifyScoreData( $value )
{
    if( $value == null || $value == "")
        return null;
    $value = (object)$value;
    $value->SELF_DISCLOSURE = $value->P;
    $value->TRUST_OF_SELF = $value->S;
    $value->TRUST_OF_OTHERS = $value->L;
    $value->SEEKING_CHANGE = $value->X;
    return $value;
}

function GetDatabyMainId( $school_data, $main_id, $assessment_type )
{
    $ass_main_ids = array_column($school_data, 'ass_main_id');
    $index = array_search( $main_id, $ass_main_ids );
    return $index;
}

function MergeCompositeBias( $composite_biases, $sci_composite_biases )
{
    $risks = $composite_biases['risks'] ?? [];
    $sci_risks = $sci_composite_biases['risks'] ?? [];
    return array_merge($risks, $sci_risks);
}

function ScoreBasedOnLabel( $label, $value )
{
    return (float)$value->{$label};
}

function buildFetchParamForNextAssessment($current_round, $current_academic_year, $school_id)
{
    if ($current_round == 3) {
        $yearList = academicYearsList($school_id);
        $academic_year = $current_academic_year + 1;
        if (!in_array($academic_year, $yearList))
            return null;
        $round = 1;
    } else {
        $round = $current_round + 1;
        $academic_year = $current_academic_year;
    }
    $filter['academic_year'][0] = $academic_year;
    $filter['round'][0] = $round;
    return $filter;
}

function buildOtherFilterParam($filter, $builtfilter) {
    $year_group = $filter['year_group'] ?? [];
    $filter['year_group'] = [];
    foreach( $year_group as $year ) {
        if( $builtfilter['academic_year'] < $filter['academic_year'] )
            $filter['year_group'][] = $year - 1;
        else
            $filter['year_group'][] = $year;
    }
    $filter['round'] = $builtfilter['round'];
    $filter['academic_year'] = $builtfilter['academic_year'];
    return $filter;
}

function buildFetchParamForPastAssessment($current_round, $current_academic_year, $school_id)
{
    if ($current_round == 1) {
        $yearList = academicYearsList($school_id);
        $academic_year = $current_academic_year - 1;
        if (!in_array($academic_year, $yearList))
            return null;
        $round = 3;//RoundLatest($academic_year);
    } else {
        $round = $current_round - 1;
        $academic_year = $current_academic_year;
    }
    $filter['academic_year'][0] = $academic_year;
    $filter['round'][0] = $round;
    return $filter;
}

function convertDate( $datetime ) {
    $date = new DateTime( $datetime );
    return $date->format('d.m.Y');
}
function SpeedType($type)
{
    if ($type == '')
        return 'NOT DEFINED';
    else if ($type == 'run')
        return 'QUICKLY';
    else if ($type == 'walk')
        return 'SLOWLY';
    else
        return 'NORMAL';
}

function GetUserRole( $level )
{
    if( $level == '1' )
        return 'pupil';
    else if( $level == '3' )
        return 'it-staff';
    else if( $level == '4' )
        return 'teacher';
    else if( $level == '5' )
        return 'senior-practitioner';
    else if( $level == '6' )
        return 'consultant';
    else if( $level == '7' )
        return 'admin';
    else
        return '';
}

function returnHighestValue( $data_in_school, $data_out_of_school )
{
    $value = $data_in_school->total() - $data_out_of_school->total();
    if( $value > 0 )
        return $data_in_school;
    else if( $value < 0 )
        return $data_out_of_school;
    return $data_in_school;
}

function SortRoundByType( $rounds )
{
    $by_campuses = array_filter($rounds, function($key) use ( $rounds ) {
        $value = (object)$rounds[$key];
        $type = strtolower( $value->name ) == "no campus" ? "Main Campus" : "campus";
        return $value->type == $type;
    }, ARRAY_FILTER_USE_KEY);
    if( !empty($by_campuses) ) {
        $by_campuses = array_values( $by_campuses );
    }

    return [ 'by_school' => [], 'by_campuses' => $by_campuses ];

}

function SortRoundByEachCampus( $campuses, $by_campuses)
{
    $data = [];
    foreach ($campuses as $campus )
    {
        $by_campus_name = array_filter($by_campuses, function($key) use ( $by_campuses, $campus ) {
            $value = (object)$by_campuses[$key];
            return strtolower($value->name) == strtolower($campus);
        }, ARRAY_FILTER_USE_KEY);
        if( !empty($by_campus_name) ) {
            $by_campus_name = array_values( $by_campus_name );
            $data[ $campus ] = $by_campus_name;
        }
    }
    return $data;
}

function returnHighestValueData( $data_in_school, $data_out_of_school )
{
    $value = count( $data_in_school ) - count( $data_out_of_school );
    if( $value > 0 )
        return $data_in_school;
    else if( $value < 0 )
        return $data_out_of_school;
    return $data_in_school;
}

function fetchvidslinks()
{
    $biases_infos = fetchvidandinfodata();
    return response()->json(
        $biases_infos
    , 200);
}

function roundInYear( $current_year, $current_round, $school_id ) {
    $yearList = array_reverse( academicYearsList($school_id) );
    $count = 1;
    $year = $current_year;
    while ( $count < 5 ) {
        $count++;
        $rounds[ $year ][] = $current_round - 1;
        $current_round = $current_round - 1;
        if( $current_round == 1 ) {
            $index = array_search( $year, $yearList );
            if( isset( $yearList[ $index + 1 ] ) ) {
                $year = $yearList[ $index + 1 ];
                $current_round = 3;
            }else {
                $count = 100;
            }


        }
    }

    return $rounds;

}

function removeRoundZero( $rounds ) {
    $index = array_search( 0, $rounds );
    if( $index > -1 )
        array_splice($rounds, $index, 1);
    return $rounds;
}

function isSchoolEligible( $school_id ) {
    $selection_type = env("UI_UX_SCHOOLS");
    if( $selection_type == "selected" ) {
        $school_ids = getUiSchoolID(); //env("UI_UX_SCHOOL_IDS");
        //$school_ids = explode(";",$school_ids);
        if( in_array( $school_id, $school_ids ) )
            return true;
    }else {
        return true;
    }

    return false;
}

function RedirectBasedOnEligibility( $school_id, $user_school_detail ) {
    if( $user_school_detail == null ) {
        $user_school_detail = Session::get('user');
        if( $user_school_detail != null ) {
            $user_school_detail = json_decode( $user_school_detail, true );
        }else {
            return false;
        }
    }

    $status = isSchoolEligible( $school_id );
    if( $status && $user_school_detail->level == 1 )
        return redirect('/ast-next/student-home');
    else if( $status && $user_school_detail->level > 1 )
        return redirect('/ast-next');

    return false;
}

function hasSafeguarding( $school_id) {
    $packages = Packages( $school_id );
    if ( in_array( 'safeguarding', $packages ) )
        return true;
    else
        return false;
}

function NameCode( $pupil_id, $studentdata = null )
{
    $dat_school = new Model_dat_schools();
    $arrYear = new Model_arr_year();
    if ( !empty(request()->target_school_id) ) { // if the target school id is not the logged school id
        $school_id = request()->target_school_id;
        $year = $dat_school->SchoolAcademicYear( $school_id );
    } else {
        $school_id = request()->school_id;
        $year  = request()->academic_year;//$dat_school->SchoolAcademicYear( $school_id );
    }
    $data = $arrYear->nameCode( $year, $pupil_id );
    if( $data == null ) {
        return generateNameCode( $studentdata );
    }else {
        return $data->value;
    }
}

function NameCode_group( $pupil_ids )
{
    $pupil_ids = explode(",",$pupil_ids);
    $dat_school = new Model_dat_schools();
    $arrYear = new Model_arr_year();
    $school_id = request()->school_id;
    $year  = $dat_school->SchoolAcademicYear( $school_id );
    $data = $arrYear->nameUsersCode($year, $pupil_ids);
    if( count($data) > 0 )
        return implode(',', $data);
    return "";

}

function CheckPermissionToRedirect( $user_id, $school_id ) {
    $dat_school_model = new Model_dat_schools();
    $staff_id = $user_id;
    $redirect_training = true;
    $academic_year = $dat_school_model->SchoolAcademicYear($school_id);
    $data = Model_new_permission::year($academic_year)
            ->select('type_permission')
            ->where('id_teacher', $staff_id)
            //->where('type_permission', 'training')
            ->first();

    if( $data ) {
        if( $data->type_permission == 'training' )
            $redirect_training = true;
        else
            $redirect_training = false;
    } else {
        $redirect_training = true;
    }

    return $redirect_training;
}

function find_common_biases(array $array,$total_students) {
    $polar_bias['POLAR_LOW_SELF_DISCLOSURE'] = $polar_bias['POLAR_HIGH_SELF_DISCLOSURE'] = $polar_bias['POLAR_HIGH_TRUST_OF_SELF'] = $polar_bias['POLAR_LOW_TRUST_OF_SELF'] = $polar_bias['POLAR_HIGH_TRUST_OF_OTHERS'] = $polar_bias['POLAR_LOW_TRUST_OF_OTHERS'] = $polar_bias['POLAR_LOW_SEEKING_CHANGE'] = $polar_bias['POLAR_HIGH_SEEKING_CHANGE'] = $composite_biases['HIDDEN_AUTONOMY'] = $composite_biases['SEEKING_CHANGE_INSTABILITY'] = $composite_biases['HIDDEN_VULNERABILITY'] = $composite_biases['SOCIAL_NAIVETY'] = $composite_biases['OVER_REGULATION'] = $counter = 0;
    $sentpolar = $final_composite = $sentcomposite = $final_polar = $final_array = [];
    foreach ($array as $arr) {
        foreach ($arr['polar_biases'] as $elem) {
            if($elem['type']=='POLAR_LOW_SELF_DISCLOSURE'){
                $polar_bias['POLAR_LOW_SELF_DISCLOSURE']++;
            }if($elem['type']=='POLAR_HIGH_SELF_DISCLOSURE'){
                $polar_bias['POLAR_HIGH_SELF_DISCLOSURE']++;
            }if($elem['type']=='POLAR_HIGH_TRUST_OF_SELF'){
                $polar_bias['POLAR_HIGH_TRUST_OF_SELF']++;
            }if($elem['type']=='POLAR_LOW_TRUST_OF_SELF'){
                $polar_bias['POLAR_LOW_TRUST_OF_SELF']++;
            }if($elem['type']=='POLAR_LOW_TRUST_OF_OTHERS'){
                $polar_bias['POLAR_LOW_TRUST_OF_OTHERS']++;
            }if($elem['type']=='POLAR_HIGH_TRUST_OF_OTHERS'){
                $polar_bias['POLAR_HIGH_TRUST_OF_OTHERS']++;
            }if($elem['type']=='POLAR_LOW_SEEKING_CHANGE'){
                $polar_bias['POLAR_LOW_SEEKING_CHANGE']++;
            }if($elem['type']=='POLAR_HIGH_SEEKING_CHANGE'){
                $polar_bias['POLAR_HIGH_SEEKING_CHANGE']++;
            }
        }
        foreach ($arr['composite_risks'] as $elem) {
            if($elem['type']=='HIDDEN_AUTONOMY'){
                $composite_biases['HIDDEN_AUTONOMY']++;
            }if($elem['type']=='SEEKING_CHANGE_INSTABILITY'){
                $composite_biases['SEEKING_CHANGE_INSTABILITY']++;
            }if($elem['type']=='HIDDEN_VULNERABILITY'){
                $composite_biases['HIDDEN_VULNERABILITY']++;
            }if($elem['type']=='SOCIAL_NAIVETY'){
                $composite_biases['SOCIAL_NAIVETY']++;
            }if($elem['type']=='OVER_REGULATION'){
                $composite_biases['OVER_REGULATION']++;
            }
        }
    }
    if(!empty($polar_bias)){
        foreach($polar_bias as $key => $polar){
            if($total_students == $polar){
                $final_polar[$counter] = $key;
                $counter++;
            }
        }
    }
    if(!empty($composite_biases)){
        foreach($composite_biases as $key => $composite){
            if($total_students == $composite){
                $final_composite[$counter] = $key;
                $counter++;
            }
        }
    }
    foreach ($array as $arr) {
        if(isset($arr['polar_biases']) && $arr['polar_biases']){
            $i=0;
            foreach ($arr['polar_biases'] as $elem) {
                if (in_array($elem['type'], $final_polar)){
                    $sentpolar['polar'][$i] = $elem;
                    $i++;
                }
            }
        }
        if(isset($arr['composite_risks']) && $arr['composite_risks']){
            $j=0;
            foreach ($arr['composite_risks'] as $elem) {
                if (in_array($elem['type'], $final_composite)){
                    $sentcomposite['composite'][$j] = $elem;
                    $j++;
                }
            }
        }
    }
    unset($i,$j);
    return array(
        'polar_biases' => isset($sentpolar['polar'])?$sentpolar['polar']:[],
        'composite_risks' => isset($sentcomposite['composite'])?$sentcomposite['composite']:[]
    );
}

function getassesmentSecondPart($index, $second_school_data, $assessment_type, $data, $rawdata)
{
    $other_rawdata = RawDataArray((object)$second_school_data[$index]);
    $value_data = ModifyScoreData((object)$second_school_data[$index]);
    $composite_biases = [];
    $risks = [];
    $compositebias = new Composite();
    if ($assessment_type == 'IN_SCHOOL' && $index > -1) {
        $composite_biases = $compositebias->StudentSCICompositeRisksObject(
            (object) $data,
            (object)$value_data,
            $rawdata,
            $other_rawdata,
            $risks
        );
    } else if ($assessment_type == 'OUT_OF_SCHOOL' && $index > -1) {
        $composite_biases = $compositebias->StudentSCICompositeRisksObject(
            (object)$value_data,
            (object)$data,
            $other_rawdata,
            $rawdata,
            $risks
        );
    }
    return $composite_biases;
}

function getDataforRound( $filter, $year, $round ) {
    foreach( $filter['historyfilter'] as $f ) {
        if( $f['academic_year'] == $year && $f['round'] == $round ) 
            return $f['assessment_list']; 
    }
    return [];
}

function compareAPDatewithAssessmentDate( $assessment_list, $next_assessment_list, $all_action_plans, $year, $round, $all_processed_ap ) {
    $student_ids = array_column($assessment_list, 'student_id');
    $next_student_ids = array_column($next_assessment_list, 'student_id');
    $update_ap = [];
    $action_plans = $all_action_plans[  $year.'-'.$round ];
    foreach( $action_plans as $key => $ap ) {
        $index = array_search( $ap->created_on, $student_ids );
        $next_index = array_search( $ap->created_on, $next_student_ids );
        if( $index > -1 ) {
            
            if( $ap->date_created > $assessment_list[$index]['completed_date'] ) {
                if( isset($next_assessment_list[0]['completed_date']) && $ap->date_created < $next_assessment_list[0]['completed_date'] )
                    $update_ap[] = $ap;
                if( count($next_assessment_list) == 0 )
                    $update_ap[] = $ap;
            }else {}
        }else {}
    }
     
    $all_processed_ap = array_merge( $all_processed_ap, $update_ap );
    return [ 'all_action_plans' => $update_ap, 'all_processed_ap' => $all_processed_ap ];
}

function CheckAndRemoveFalseDataByRoundHistoric( $data, $filter ) {
   
    if( !$data ) 
        return $data;

    $key_values = array_keys( $data );
    $all_processed_ap = [];
    $assMain = new Model_ass_main();
    foreach( $key_values as $v ) {
        $year = explode('-', $v )[0];
        $round = explode('-', $v )[1];
        $assessment_list = getDataforRound( $filter, $year, $round );
        if( $round == 3 ){ $next_round = 1; $next_year = $year + 1; }
        else { $next_round = $round + 1; $next_year = $year; }
        $filter['academic_year'][0] = $year; 
        $filter['round'][0] = $round; 
        $tempfilter = getNextRoundAP($filter);
        $next_round = $tempfilter['round'][0] ?? 1;
        $next_year = $tempfilter['academic_year'][0] ?? $year;
        $next_assessment_list = getDataforRound( $filter, $next_year, $next_round );
        if( count($next_assessment_list) == 0 ) {
            $next_assessment_list = $assMain->getAssessmentReport($tempfilter, ['sch','hs']);
        }
       
        [ 'all_action_plans' => $all_action_plans, 'all_processed_ap' => $all_processed_ap ] = compareAPDatewithAssessmentDate( $assessment_list, $next_assessment_list, $data, $year, $round, $all_processed_ap );
        $action_plan_list[ $year.'-'.$round ] = $all_action_plans;
    }
    return $action_plan_list;
}

function CheckAndRemoveFalseDataByRound( $data, $filter, $type ) {

    $update_ap = [];
    $assMain = new Model_ass_main();
    $assessment_list = $assMain->getAssessmentReport($filter, $type);
    $tempfilter = getNextRoundAP($filter);
    $update_ap = [];
    $next_assessment_list = $assMain->getAssessmentReport($tempfilter, $type);
    $student_ids = array_column($assessment_list, 'student_id');
    $next_student_ids = array_column($next_assessment_list, 'student_id');
    foreach( $data as $ap ) {
        $index = array_search( $ap->created_on, $student_ids );
        $next_index = array_search( $ap->created_on, $next_student_ids );
        if( $index > -1 ) {
                if( $ap->date_created > $assessment_list[$index]['completed_date'] ) {
                    if( isset($next_assessment_list[0]['completed_date']) ) {
                        if( $ap->date_created < $next_assessment_list[0]['completed_date'] )
                            $update_ap[] = $ap;
                    }else {
                        $update_ap[] = $ap;
                    }
            }
            
        }
    }
    return $update_ap;
}

function getNextRoundAP($filter) {
    $ass_main = new Model_ass_main();
    $max_round_in_year = $ass_main->round($filter['academic_year'][0]);
    if( $max_round_in_year > $filter['round'][0] ) {
        $tempfilter['round'][0] = $filter['round'][0] + 1; 
        $tempfilter['academic_year'][0] = $filter['academic_year'][0];
    }
    else {
        $actionPlanMeta = new ActionPlanMetaServiceProvider();
        $yearList = $actionPlanMeta->academicYearsList( request()->school_id );
        
        if( in_array( ($filter['academic_year'][0] + 1),$yearList ) ) {
            $tempfilter['round'][0] = 1;
            $tempfilter['academic_year'][0] = $filter['academic_year'][0] + 1;
        }else {
            $tempfilter['round'][0] = $filter['round'][0] + 1; 
            $tempfilter['academic_year'][0] = $filter['academic_year'][0];
        }
    }
    return $tempfilter;

}

function getImmediateLastAssessment( $filter ) {
    $ass_main = new Model_ass_main();
    $min_round_in_year = $ass_main->getMinRound($filter['academic_year'][0]);
    if( $filter['round'][0] > $min_round_in_year ) {
        $filter['round'][0] = $filter['round'][0] - 1; 
        $filter['academic_year'][0] = $filter['academic_year'][0];
    }
    else {
        $actionPlanMeta = new ActionPlanMetaServiceProvider();
        $yearList = $actionPlanMeta->academicYearsList( request()->school_id );
        
        if( in_array( ($filter['academic_year'][0] - 1),$yearList ) ) {
            $max_round_in_year = $ass_main->round( $filter['academic_year'][0] - 1 );
            $filter['round'][0] = $max_round_in_year ?? 1;
            $filter['academic_year'][0] = $filter['academic_year'][0] - 1;
        }else {
            $filter['round'][0] = -4;// this is a round value to avoid picking wrong value //$filter['round'][0] - 1; 
            $filter['academic_year'][0] = $filter['academic_year'][0];
        }
    }
    return $filter;
}

function IsAllDataPresent( $school_data, $school_id, $assessment_type ) {
    if( $school_data == null ) {
        $packages = Packages( $school_id );
        if( $assessment_type == 'IN_SCHOOL' )
            return 'INCOMPLETE';
        if( in_array( 'safeguarding', $packages ) && $assessment_type == 'OUT_OF_SCHOOL' )
            return 'INCOMPLETE';
        else
            return 'COMPLETED';
    }
    for ($i = 1; $i <= 16; $i++) {
        if ( $i < 10 ) {
            if( $school_data->{"q0" . $i} == null || $school_data->{"q0" . $i} == '' )
                return 'INCOMPLETE';
        }
        else if ( $i <= 16 ) {
            if( $school_data->{"q" . $i} == null || $school_data->{"q" . $i} == '' )
                return 'INCOMPLETE';
        }
    }

    if( $school_data->P == null || $school_data->P == '' )
        return 'INCOMPLETE';
    if( $school_data->S == null || $school_data->S == '' )
        return 'INCOMPLETE';
    if( $school_data->L == null || $school_data->L == '' )
        return 'INCOMPLETE';
    if( $school_data->X == null || $school_data->X == '' )
        return 'INCOMPLETE';
    
    return 'COMPLETED';
}
function variantUpdate( $variants, $trend_composite_biases, $in_school_composite_biases, $out_of_school_composite_biases ) {
    $labels = array_column( $variants, 'label' );
    $in_index = array_search( 'IN_SCHOOL', $labels );
    $out_index = array_search( 'OUT_OF_SCHOOL', $labels );
    if( $in_index > -1 ) {  
        $in_school = isset( $in_school_composite_biases ) ? $in_school_composite_biases : [];
        $trend = $trend_composite_biases['IN_SCHOOL'];
        $variants[$in_index]['trend_composite_biases'] = array_merge( $in_school, $trend ); 
    }
       
    if( $out_index > -1 ) {
        $out_school = isset( $out_of_school_composite_biases ) ? $out_of_school_composite_biases : [];
        $trend = $trend_composite_biases['OUT_OF_SCHOOL'];
        $variants[$out_index]['trend_composite_biases'] = array_merge( $out_school, $trend ); 
    }
        
    
    return $variants;
}

function find_stages($type){
    if($type=='Design Tutorial')
        $prefix = 'dt';
    if($type=='Planning Tutorial')
        $prefix = 'pt';
    if($type=='Assessment 1')
        $prefix = 'ar1';
    if($type=='Post Assessment Tutorial 1')
        $prefix = 'pat1';
    if($type=='Mid year Review' || $type=='Mid Year Review')
        $prefix = 'myr';
    if($type=='Assessment 2')
        $prefix = 'ar2';
    if($type=='Post Assessment Tutorial 2')
        $prefix = 'pat2';
    if($type=='Assessment 3')
        $prefix = 'ar3';
    if($type=='Post Assessment Tutorial 3')
        $prefix = 'pat3';
    if($type=='End of year review' || $type=='End of Year review')
        $prefix = 'eyr';
    return $prefix;
}

function find_fullname($type){
    if($type=='dt')
        $prefix = 'Design Tutorial';
    if($type=='pt')
        $prefix = 'Planning Tutorial';
    if($type=='ar1')
        $prefix = 'Assessment 1';
    if($type=='pat1')
        $prefix = 'Post Assessment Tutorial 1';
    if($type=='myr')
        $prefix = 'Mid Year Review';
    if($type=='ar2')
        $prefix = 'Assessment 2';
    if($type=='pat2')
        $prefix = 'Post Assessment Tutorial 2';
    if($type=='ar3')
        $prefix = 'Assessment 3';
    if($type=='pat3')
        $prefix = 'Post Assessment Tutorial 3';
    if($type=='eyr')
        $prefix = 'End of Year Review';
    return $prefix;
}

function find_shortname($type){
    if($type=='Design Tutorial')
        $shortname = 'design_tutorial_date';
    elseif($type=='Planning Tutorial')
        $shortname = 'post_tanining_q_and_a_date';
    elseif($type=='Mid year Review')
        $shortname = 'mid_year_review_date';
    elseif($type=='End of year review')
        $shortname = 'end_of_year_review_date';
    else
        $shortname = null;
    return $shortname;
}

function find_shortnames($type){
    if($type=='Assessment 1'){
        $startdate = 'assessment_1_date_1';
        $enddate = 'assessment_1_date_2';
    }
    elseif($type=='Post Assessment Tutorial 1'){
        $startdate = 'post_assessment_tutorial_1_date_1';
        $enddate = 'post_assessment_tutorial_1_date_2';
    }
    elseif($type=='Assessment 2'){
        $startdate = 'assessment_2_date_1';
        $enddate = 'assessment_2_date_2';
    }
    elseif($type=='Post Assessment Tutorial 2'){
        $startdate = 'post_assessment_tutorial_2_date_1';
        $enddate = 'post_assessment_tutorial_2_date_2';
    }
    elseif($type=='Assessment 3'){
        $startdate = 'assessment_3_date_1';
        $enddate = 'assessment_3_date_2';
    }
    elseif($type=='Post Assessment Tutorial 3'){
        $startdate = 'post_assessment_tutorial_3_date_1';
        $enddate = 'post_assessment_tutorial_3_date_2';
    }
    else{
        $startdate = null;
        $enddate = null;
    }
    return [$startdate,$enddate];
}

function GroupdashPercentageCalculation($total, $count){
    if( $total == 0 )
        $total = 1;
    $percent = ( $count * 100 ) / $total;
    return round($percent);
}

function HistoryFilter( $filter, $years ) {
    $historyfilter = [];
    while( $filter != null ) {
        if ($filter['round'][0] == 1) {
            if (!in_array( ( $filter['academic_year'][0] - 1 ), $years))
                $filter = null;
            else {
                $filter['academic_year'][0] = $filter['academic_year'][0] - 1;
                $filter['round'][0] = 3;
            }
            
        } else {
            $filter['round'][0] = $filter['round'][0] - 1;
        }

        if( $filter != null )
            $historyfilter[] = [ 'academic_year' => $filter['academic_year'][0], 'round' => $filter['round'][0] ];
    }
    return $historyfilter;
}

function formatPastAssessmentData( $past_assessment ) {
    $data = [];
    $polarRisk = new PolarRisk();
    foreach( $past_assessment as $ass ) {
        $value = ModifyScoreData( (object)$ass );
        $count = $polarRisk->StudentPolarRisks( $value );
        $data[ $value->student_id.'_continue' ] = $count >= 2 ? true : false;
        $data[ $value->student_id ] = isset( $data[ $value->student_id ] ) ? $data[ $value->student_id ] : 1;
        if( $data[ $value->student_id.'_continue' ] == true )
            $data[ $value->student_id ] = $data[ $value->student_id ] + 1;

    }

    return $data;
}

function calculate_progress($stage,$current_stage){
    $progress = 0;
    if($stage=='launch') {
        if($current_stage=='Design Tutorial')
            $progress = 10;
        elseif($current_stage=='Planning Tutorial')
            $progress = 20;
        elseif($current_stage=='Assessment 1')
            $progress = 30;
        elseif($current_stage=='Post Assessment Tutorial 1')
            $progress = 40;
        elseif($current_stage=='Mid Year Review')
            $progress = 50;
        elseif($current_stage=='Assessment 2')
            $progress = 60;
        elseif($current_stage=='Post Assessment Tutorial 2')
            $progress = 70;
        elseif($current_stage=='End of Year Review/Design Tutorial')
            $progress = 80;
        elseif($current_stage=='Assessment 3')
            $progress = 90;
        elseif($current_stage=='Post Assessment Tutorial 3')
            $progress = 100;
    }else{
        if($current_stage=='Planning Tutorial')
            $progress = 14.285;
        elseif($current_stage=='Assessment 1')
            $progress = 28.57;
        elseif($current_stage=='Post Assessment Tutorial 1')
            $progress = 42.855;
        elseif($current_stage=='Mid Year Review')
            $progress = 57.14;
        elseif($current_stage=='Assessment 2')
            $progress = 71.426;
        elseif($current_stage=='Assessment 3')
            $progress = 85.71;
        elseif($current_stage=='End of Year Review/Design Tutorial')
            $progress = 100;
    }
    return $progress;
}

function format_colour($stage,$year,$selected_schoolid,$sub_id){
    $model_dat_school_review = new Model_dat_school_review();
    if($stage=='launch')
        $otherdata = ['dt','pt','ar1','pat1','myr','ar2','pat2','ar3','pat3','eyr'];
    else
        $otherdata = ['pt','ar1','pat1','myr','ar2','ar3','eyr'];
    $totalstages = count($otherdata);
    $final_data = [];
    $counter= 0;
    if(isset($otherdata[0])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[0]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour = 'red';
            if($status['rag']=='amber')
                $colour = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[0]);
        $final_data[$counter]['colour'] = isset($colour)?$colour:null;
        $counter++;
    }if(isset($otherdata[1])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[1]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour1 = 'red';
            if($status['rag']=='amber')
                $colour1 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[1]);
        $final_data[$counter]['colour'] = isset($colour1)?$colour1:null;
        $counter++;
    }if(isset($otherdata[2])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[2]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour2 = 'red';
            if($status['rag']=='amber')
                $colour2 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[2]);
        $final_data[$counter]['colour'] = isset($colour2)?$colour2:null;
        $counter++;
    }if(isset($otherdata[3])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[3]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour3 = 'red';
            if($status['rag']=='amber')
                $colour3 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[3]);
        $final_data[$counter]['colour'] = isset($colour3)?$colour3:null;
        $counter++;
    }if(isset($otherdata[4])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[4]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour4 = 'red';
            if($status['rag']=='amber')
                $colour4 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[4]);
        $final_data[$counter]['colour'] = isset($colour4)?$colour4:null;
        $counter++;
    }if(isset($otherdata[5])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[5]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour5 = 'red';
            if($status['rag']=='amber')
                $colour5 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[5]);
        $final_data[$counter]['colour'] = isset($colour5)?$colour5:null;
        $counter++;
    }if(isset($otherdata[6])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[6]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour6 = 'red';
            if($status['rag']=='amber')
                $colour6 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[6]);
        $final_data[$counter]['colour'] = isset($colour6)?$colour6:null;
        $counter++;
    }if(isset($otherdata[7])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[7]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour7 = 'red';
            if($status['rag']=='amber')
                $colour7 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[7]);
        $final_data[$counter]['colour'] = isset($colour7)?$colour7:null;
        $counter++;
    }if(isset($otherdata[8])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[8]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour8 = 'red';
            if($status['rag']=='amber')
                $colour8 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[8]);
        $final_data[$counter]['colour'] = isset($colour8)?$colour8:null;
        $counter++;
    }if(isset($otherdata[9])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[9]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour9 = 'red';
            if($status['rag']=='amber')
                $colour9 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[9]);
        $final_data[$counter]['colour'] = isset($colour9)?$colour9:null;
        $counter++;
    }if(isset($otherdata[10])){
        $status = $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$otherdata[10]);
        if(isset($status['rag'])){
            if($status['rag']=='red')
                $colour10 = 'red';
            if($status['rag']=='amber')
                $colour10 = 'amber';
        }
        $final_data[$counter]['id'] = $counter+1;
        $final_data[$counter]['label'] = find_fullname($otherdata[10]);
        $final_data[$counter]['colour'] = isset($colour10)?$colour10:null;
        $counter++;
    }
    return $final_data;
}

function determine_current_stage($round){
    if($round == 1)
       $stage = 'Assessment 1';
    elseif($round == 2)
        $stage = 'Assessment 2';
    else
        $stage= 'Assessment 3';
    return $stage;
}

function IsTrackingPlatformGroup( $user ) {
    if( $user->school_id == 37 ) {
        $multiSchool = new Model_multischools;
        $school_data_attached = $multiSchool->userSchool( $user->id );
        if( $school_data_attached && ( $school_data_attached->schools == 'zenith' || $school_data_attached->schools == 'unitedlearning' || $school_data_attached->schools == 'fcat') )
            return true;
        return false;
    }
    return false;
}

function like_search_r($array, $key, $value, array &$results = []){
    if (!is_array($array)) {
        return;
    }
    $key   = (string)$key;
    $value = (string)$value;
    foreach ($array as $arrayKey => $arrayValue) {
        if ( matchstring($key, $arrayKey) && matchstring($value, $arrayValue)) {
            // add array if we have a match
            $results[] = $array;
        }if (is_array($arrayValue)) {
            // only do recursion on arrays
            like_search_r($arrayValue, $key, $value, $results);
        }
    }
}

function matchstring($search, $subject){
    $search = str_replace('/', '\\/', $search);
    return preg_match("/$search/i", (string)$subject);
}

function removeElementWithValue($array, $key, $value){
    foreach($array as $subKey => $subArray){
         if($subArray[$key] == $value){
              unset($array[$subKey]);
         }
    }
    return $array;
}

function correct_stage( $stage ) {
    $stage_present  = 'Launch';
    if($stage == 'launch')
        $stage_present = 'Launch';
    elseif($stage == 'rollout')
        $stage_present = 'Roll Out';
    else
        $stage_present = 'accredited';
    return $stage_present;
}
function correct_launch( $stage ) {
    if($stage == 'launch')
        $stage_present = 1;
    else
        $stage_present = 0;
    return $stage_present;
}

function slider_array( $value ) {
    $otherdata = [];
    if($value=='launch')
        $otherdata = ['dt','pt','ar1','pat1','myr','ar2','pat2','ar3','pat3','eyr'];
    else
        $otherdata = ['pt','ar1','pat1','myr','ar2','ar3','eyr'];
    return $otherdata;
}

function consultant($schoolid){
    $model_sga_auth = new Model_sga_auth();
    $con_query = $model_sga_auth->getconsultant();
    foreach ($con_query as $con_data) {
        $con_ids[] = $con_data['id'];
        $abb = explode(" ", $con_data['user_name']);
        $name = "";
        foreach ($abb as $ab) {
            $name .= $ab;
        }
        $consultants[$con_data['user_name']] = explode(",", $con_data['schools']);
    }
    $consultant = '-';
    foreach ($consultants as $key => $con) {
        if (in_array($schoolid, $con))
            $consultant = $key;
    }
    return $consultant;
}

function dates_groupdash($schoolid,$sub_id,$type){
    $shortnames = find_shortnames($type);
    $model_dat_school_tracking = new Model_dat_school_tracking();
    $ass_start_data = $model_dat_school_tracking->gettrackingfields($schoolid,$shortnames[0],$sub_id);
    $ass_end_data = $model_dat_school_tracking->gettrackingfields($schoolid,$shortnames[1],$sub_id);
    $start_date = isset($ass_start_data['value'])?date("d/m/Y", strtotime($ass_start_data['value'])):null;
    $end_date = isset($ass_end_data['value'])?date("d/m/Y", strtotime($ass_end_data['value'])):null;
    if($start_date!=null && $end_date!=null)
        $fulldate = $start_date.'-'.$end_date;
    else
        $fulldate = null;
    $date_value = $fulldate;
    return $date_value;
}

function status_fetch($year,$selected_schoolid,$selected_subschoolid,$type){
    $model_dat_school_review = new Model_dat_school_review();
    $sub_id = isset($selected_subschoolid)?$selected_subschoolid:0;
    $prefix = find_stages($type);
    $status =  $model_dat_school_review->getImplementation($year,$selected_schoolid,$sub_id,$prefix);
    $implementation = 'none';
    if(isset($status['rag'])){
        if($status['rag']=='red')
            $implementation = 'Action Required';
        if($status['rag']=='amber')
            $implementation = 'On Track';
    }
    return $implementation;
}

function fetch_ssp($school_id,$sub_id){
    DB::disconnect('schools');
    dbSchool( $school_id );
    $ssp_display = $order = null;
    $model_portal_school_info = new Model_portal_school_info();
    $or = $model_portal_school_info->fetch_order($sub_id);
    if(isset($or['order']) && $or['order'] != "" )
        $order = $or['order'];
    $ssp = $model_portal_school_info->getSSPInfo($sub_id,$order);
    if(isset($ssp['value']) && $ssp['value'] != "" )
        $ssp_display = $ssp['value'];
    return $ssp_display;
}

function displayschool($is_subschool,$school_data,$sub_id){
    $display_school_name = '';
    if($is_subschool == "false" || $is_subschool == false){
        $display_school_name = $school_data['name'];
    }else{
        $con['id'] = $sub_id;
        $arr_subschools_model = new Model_arr_subschools();
        $subname = $arr_subschools_model->getSubSchoolById($con);
        $display_school_name = isset($subname['name'])?$subname['name']:'No name set';
        $display_school_name = $school_data['name'] .'-' . $display_school_name;
    }
    return $display_school_name;
}

function isJson($string) {
    $str = json_decode($string);
    if(isset($str[0]->student_id)){
        return 'true';
    }else{
        return 'false';
    }
}

function Group_Review_student( $review ,$student_id )
{
    if(isJson($review)=='true'){
        $review_data = json_decode($review, TRUE);
        $final_review_array = [];
        foreach ($review_data as $re) {
            if($re['student_id'] == $student_id ){
                $final_review = $re['review'];
                if( $final_review == null | $final_review == '')
                    return "Not Reviewed";
                if( $final_review == "POSITIVE_IMPACT")
                    return "Positive Impact";
                else
                    return "No Impact yet";
            }
        }

    }else{
        if( $review == null || $review == "")
            return "Not Reviewed";
        else if( $review == "POSITIVE_IMPACT")
            return "Positive Impact";
        return "No Impact yet";
    }
}
function IsSchoolNameCode() {
    $dat_school = new Model_dat_schools();
    $school_detail = $dat_school->SchoolDetail( request()->school_id );
    return $school_detail->name_code == 'y' ? true : false;
}

function Replacefirst($remove,$replace,$s)
{
    $s = strrev( $s );
    $w=strpos($s,$remove);
    if($w===false)return $s;
    return substr($s,0,$w).$replace.substr($s,$w+strlen($remove));
}

function getUserNameCode( $value ) {
    if( isset( $value->name_code) ) {
        $str_count = substr_count( $value->name_code, "_" );
        if( $str_count > 1 ) {
            $name_code =Replacefirst( '_',' ',$value->name_code );
            $name_code = strrev( $name_code );
            return $name_code;
        } 
        return $value->name_code.' '.$value->lastname ?? null;
    }
    return generateNameCode( $value );
}



function IsManipulated( $answer ) {
    if( $answer == null ) {
        return false;
    }
    if ( preg_match("/1,1,1,1,1,1,1,1/i", $answer)) {
        return true;
    }
    else if( preg_match("/1,2,3,4,5,6/i", $answer )) {
        return true;
    }
    else if ( preg_match("/1,6,1,6,1,6,1,6/i", $answer)) {
        return true;
    }
    else if( preg_match("/2,2,2,2,2,2,2,2,2/i", $answer )) {
        return true;
    }
    else if ( preg_match("/2,5,2,5,2,5,2,5/i", $answer)) {
        return true;
    }
    else if( preg_match("/3,3,3,3,3,3,3,3,3/i", $answer )) {
        return true;
    }
    else if ( preg_match("/3,4,3,4,3,4,3,4,3,4,3/i", $answer)) {
        return true;
    }
    else if( preg_match("/4,4,4,4,4,4,4,4/i", $answer )) {
        return true;
    }
    else if ( preg_match("/5,1,5,1,5,1,5,1/i", $answer)) {
        return true;
    }
    else if( preg_match("/5,2,5,2,5,2,5,2/i", $answer )) {
        return true;
    }
    else if ( preg_match("/5,3,5,3,5,3,5,3/i", $answer)) {
        return true;
    }
    else if( preg_match("/5,4,5,4,5,4,5,4/i", $answer )) {
        return true;
    }
    else if( preg_match("/5,5,5,5,5,5,5,5/i", $answer )) {
        return true;
    }
    else if ( preg_match("/6,2,6,2,6,2,6,2/i", $answer)) {
        return true;
    }
    else if( preg_match("/6,3,6,3,6,3,6,3/i", $answer )) {
        return true;
    }
    else if ( preg_match("/6,4,6,4,6,4,6,4/i", $answer)) {
        return true;
    }
    else if( preg_match("/6,5,4,3,2,1/i", $answer )) {
        return true;
    }
    else if ( preg_match("/6,5,6,5,6,5,6,5/i", $answer)) {
        return true;
    }
    else if( preg_match("/6,6,6,6,6,6,6,6/i", $answer )) {
        return true;
    }
    
    return false;
}

function find_type_ass_tracker($stage,$year,$school_id,$custom_sub_id){
    $model_dat_school_review = new Model_dat_school_review();
    $str_school_overview_model = new Model_str_school_overview();
    if($stage=='Launch')
        $stage = 'launch';
    $otherdata = slider_array( $stage );
    $phase =  $model_dat_school_review->getSchoolReviews($year,$school_id,$custom_sub_id);
    if(!empty($phase)){
        $phase = end($phase);
        if (in_array($phase['phase'], $otherdata)){
            $key_phase =  array_search($phase['phase'], $otherdata);
            $current_stage = $otherdata[$key_phase+1];
        }else{
            $current_stage = $otherdata[0];
        }
    }else{
        $phase['phase'] = $otherdata[0];
        $current_stage = $otherdata[0];
    }
    $getphases = $str_school_overview_model->getPhase($current_stage);
    if(isset($getphases['tooltip']) && !empty($getphases['tooltip']))
        return 	$getphases['tooltip'];
    else
        return null;
}

function getUserMetaData($decrypted_user) {
    $user_id = explode("-", $decrypted_user)[0] ?? null;
    $school_id = explode("-", $decrypted_user)[1] ?? null;
    if( !$user_id && !$school_id)
        return null;
    dbSchool($school_id);
    $population_model = new Model_population();
    $dat_school = new Model_dat_schools();
    $school_details = $dat_school->SchoolDetail($school_id);
    $user = $population_model->get($user_id);
    $data['session_data']['session']['user']['level'] = $user->level ?? null;
    $data['session_data']['session']['user']['id'] = $user->id ?? null;
    $data['session_data']['session']['user']['firstname'] = $user->firstname ?? null;
    $data['session_data']['session']['user']['lastname'] = $user->lastname ?? null;
    $data['session_data']['session']['user']['dob'] = $user->dob ?? null;
    $data['session_data']['school_code'] = $school_details->urn ?? null;
    $data['session_data']['session']['lang_id'] = "";
    $data['session_data']['session']['user']['name'] = $user->firstname ?? null;
    $data['session_data']['school_id'] = $school_id;
    return $data;
}

function getCampuses() {
    $arrSubSchool = new Model_arr_subschools();
    $campuses = $arrSubSchool->getCampuses();
    if( count($campuses) == 1 ) {
        $name = $campuses[0]->name;
        if( $name == '0') return 'no_campus';
        else return "named_campus";
    }
    else if( count($campuses) > 1 ) 
        return "multi_campus";
    else 
        return "unknown";

}

function decryptUserData($request) {
    try{
        $school_id = Crypt::decryptString($request->get('school')) ?? null;
        $user_email = Crypt::decryptString($request->get('u')) ?? null;
        dbSchool($school_id);
        $pop = new Model_population();
        $user = $pop->getNewUserByEmail($user_email);
        return [ 'user' => $user, 'school_id' => $school_id ];
    }catch(Exception $ex) {
        return [ 'user' => null, 'school_id' => null ];
    }
}

function getNewYearSetUpStatus($school_id) {
    $rollover = new Model_rollover_communication();
    $data = $rollover->getSchoolRollOver( $school_id );
    if( $data == null ) return null;
    return [ 
        "old_school_setup_status" => (int)$data->is_rollover ?? 0,
        "old_school_setup_stage" =>  (int)$data->phase ?? 0, 
        "setup_completed_status" => (int)$data->is_setup_completed ?? 0
    ];
}

function removeUnWantedAssessmentData( $data ) {
    foreach($data as $key => $record ) {
        $data[$key] = collect($record)->except(['field', 'value', 'is_manipulated', 'speed', 'PR', 'R', 'W', 'C', 'N', 'M', 'V', 'O', 'F', 'T',
                         'is_completed', 'completed_date', 'enc_score_id', 'id', 'sid','qid', 'pop_id','datetime','ref', 'ass_main_id', 'round','school_id'
                         ])->all();
    }
    return $data;
}

function getCampusImported( $year ) {
    $arrYear = new Model_arr_year();
    return $arrYear->getSpecificGroup($year, 'campus');
}

function getSearchSchoolList($conditions) {
    $name = $conditions['school_name'] ?? "";
    $list = getAllActiveSchool();
    if( count($list) == 0 ) {
        $list = ( new Model_dat_schools )->getAllSchoolList();
        setAllActiveSchool($list);
    }
    $data =  collect($list)->filter(function ($q) use ($name) {
        return Str::startsWith( strtolower($q->school_name), strtolower($name));
    });
    return array_values($data->toArray()) ?? [];
}

function getGlobalSchoolList($conditions) {
    $first_character = $conditions['school_name'][0] ?? null;
    $name = $conditions['school_name'] ?? "";
    $first_character = strtoupper($first_character);
    if( $first_character == null ) return [];
    $list = getAllSchoolList( $first_character ); 
    if( count($list) == 0 ) {
        $list = ( new Model_dat_school_list )->getAllSchoolListByGroup( $first_character );
        setAllSchoolList( $first_character, $list );
    }
    $data = collect($list)->filter(function ($q) use ($name) {
        return Str::startsWith( strtolower($q->school_name), strtolower($name));
    });
    return array_values($data->toArray()) ?? [];
}

function generateNameCode( $value ) {
    if( $value == null ) return null;
    if( !empty($value->firstname) ) {
        $name = lcfirst($value->firstname);
        $list = preg_split('/(?=[A-Z])/',$name);
        $firstname = substr($list[0] ?? "", 0, 3);
        $lastname = substr($list[1] ?? "", 0, 1);
        $mis_id = substr($value->mis_id ?? "", -4);
        $name_code = strtoupper($firstname.''.$lastname);
        if( $mis_id ) $mis_id = '_'.$mis_id;
        return $name_code.''.$mis_id.' '. $value->lastname ?? "";
    }
    return null;
}

function getConsultantURL($name) {
    $url = 'https://steereducation.setmore.com';
    if( $name == 'Trevor Greenhill') return "$url/trevor-greenhill";
    else if( $name == 'Lesley Chandler') return "$url/lesley-chandler";
    else if( $name == 'Robert Lloyd Williams') return "$url/robert-lloyd-williams";
    else if( $name == 'Clare Sergeant') return "$url/clare-sergeant";
    else if( $name == 'Nick Digby') return "$url/nick-digby";
    else return null;
}

function AddNameCodeToStudentObject( $pupils) {
    $isNameCode = IsSchoolNameCode();
    foreach( $pupils as $key => $pupil ) {
        if( $isNameCode == true )
            $pupils[$key]['name_code'] = $pupil->firstname.' '.$pupil->lastname;
        else 
            $pupils[$key]['name_code'] = getUserNameCode($pupil);
    }
    return $pupils;
}

function DBConnection( $school_id ) 
{
    try{
        DB::disconnect('schools');
        DB::purge('schools');
        dbSchool( $school_id );
    }catch( \Illuminate\Database\QueryException $ex ) {
        echo $ex;
    }
    
}

