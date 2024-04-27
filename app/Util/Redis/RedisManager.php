<?php

namespace App\Util\Redis;


abstract class RedisManager
{
    const REDIS_INSTANCES = [];
    const REDIS_KEY = null;
    const TTL = 60 * 60 * 24;

    abstract public function updateSingleRecord(string $id): bool;

    abstract public function updateAllRecord(string $type): void;

    abstract public function getRecordByTypeAndCalculation(string $type, string $year, string $school_id);

    abstract public function setRecordByTypeAndCalculation(string $type,string $year, string $school_id, array $data);

    abstract public function getRecordByTypeAndPage(string $type, int $page, string $school_id, array $filter);

    abstract public function updateRecordByTypeAndPage(string $type, string $page, string $school_id, array $filter, $data);

    abstract public function updatePupilList(string $academic_year, string $school_id, array $data);

    abstract public function getPupilList(string $academic_year, string $school_id);

    abstract public function getRecordByTypeByPageAndCalculation(string $type, string $page, string $size, string $year, string $school_id);

    abstract public function setRecordByTypeByPageAndCalculation(string $type, string $page, string $size, string $year, string $school_id, $data);

    public function refreshAllRecord(): void
    {

    }

}

