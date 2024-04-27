<?php

namespace App\Util\Redis;
use App\Util\Redis\RedisManager;
use App\Models\Dbshools\Model_population;
use Illuminate\Support\Facades\Redis as RedisFacade;

class AssessmentTrackerManager extends RedisManager {

    const REDIS_INSTANCES = ["redis_instance"];
    const REDIS_KEY       = 'assessmenttracker';
    public function updateSingleRecord(string $id): bool
    {

        return true;
    }

    public function updateAllRecord(string $type): void
    {

    }

    public function getRecordByTypeAndCalculation(string $type, string $year,string  $school_id)
    {
        $assessmentTracker = RedisFacade::get(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$year.'.'.$school_id.'');

        if(isset($assessmentTracker))
            return unserialize($assessmentTracker);

        return null;
    }

    public function setRecordByTypeAndCalculation(string $type, string $year, string $school_id, array $data)
    {
        $data = serialize($data);
        $assessmentTracker = RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$year.'.'.$school_id.'', $data, 'EX', RedisManager::TTL);
        return true;
    }


    public function getRecordByTypeAndPage(string $type, int $page, string $school_id, array $filter)
    {
        $filter = encrypt_decrypt('encrypt', json_encode($filter) );
        $assessmentTracker = RedisFacade::get(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$page.'.'.$filter.'.'.$school_id.'');

        if(isset($assessmentTracker))
            return unserialize($assessmentTracker);

        return null;
    }

    public function updateRecordByTypeAndPage(string $type, string $page, string $school_id, array $filter, $data)
    {
        $filter = encrypt_decrypt('encrypt', json_encode($filter) );
        $data = serialize($data);
        $assessmentTracker = RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$page.'.'.$filter.'.'.$school_id.'', $data, 'EX', RedisManager::TTL);

        return true;
    }

    public function updatePupilList(string $academic_year, string $school_id, $data)
    {
        $data = serialize($data);
        RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.pupillist_rawdata.'.$academic_year.''.$school_id.'', $data);

        return true;
    }

    public function getPupilList(string $academic_year, string $school_id)
    {
        $pupillist = RedisFacade::get(AssessmentTrackerManager::REDIS_KEY.'.pupillist_rawdata.'.$academic_year.''.$school_id.'');

        if(isset($pupillist))
        {
            $data = unserialize($pupillist);
            return $data;
        }

        return null;

    }

    public function updatePupilListWithCalculatedData($data)
    {
        $data = serialize($data);
        $pupillist = RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.pupillist_calculateddata', $data);

        return null;

    }

    public function getRecordByTypeByPageAndCalculation(string $type, string $page, string $size, string $year, string $school_id) {
        $data = RedisFacade::get(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$page.'.'.$size.'.'.$year.'.'.$school_id.'');

        if(isset($data))
        {
            $data = unserialize($data);
            return $data;
        }

        return null;
    }

    public function setRecordByTypeByPageAndCalculation(string $type, string $page, string $size, string $year, string $school_id, $data) {
        $data = serialize($data);

        RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.'.$type.'.'.$page.'.'.$size.'.'.$year.'.'.$school_id.'', $data);

        return true;
    }

    public function setRecordByTypeByYearAndCalculationCount(string $type, string $size, string $year, string $school_id, $data) {
        $data = serialize($data);

        RedisFacade::set(AssessmentTrackerManager::REDIS_KEY.'.size.'.$type.'.'.$size.'.'.$year.'.'.$school_id.'', $data);

        return true;
    }

    public function getRecordByTypeByYearAndCalculationCount(string $type, string $size, string $year, string $school_id) {

        $data = RedisFacade::get(AssessmentTrackerManager::REDIS_KEY.'.size.'.$type.'.'.$size.'.'.$year.'.'.$school_id.'');

        if(isset($data))
        {
            $data = unserialize($data);
            return $data;
        }

        return null;
    }






}
