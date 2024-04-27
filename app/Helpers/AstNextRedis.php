<?php
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

function setuserIdEncryptedValue( $en_user_id ) {
    try {
        $redis = Redis::connection();
        $redis->set('encrypted'.$en_user_id.'', $en_user_id );
    } catch (Predis\Connection\ConnectionException $ex) {
        return false;
    }
    return true;
}

function getuserIdEncryptedValue( $en_user_id ) {
    try {
        $redis = Redis::connection();
        return $redis->get('encrypted'.$en_user_id.'' );
    } catch (Predis\Connection\ConnectionException $ex) {
       return $en_user_id;
    }
}

function setallUserdata( $data, $en_user_id ) {
    try {
        $redis = Redis::connection();
        $data = json_encode( $data );
        $redis->set('data'.$en_user_id.'', $data );
    } catch (Predis\Connection\ConnectionException $ex) {
        return false;
    }
    return true;
}

function getallUserdata( $en_user_id ) {
    try {
        $redis = Redis::connection();
        $data = $redis->get('data'.$en_user_id.'' );
        $data = json_decode( $data, true ) ?? null;
    } catch (Predis\Connection\ConnectionException $ex) {
        $data = null;
        if (Session::exists("response_data")) {
            $response_data = Session::get('response_data');
            $data = json_decode( $response_data, true ) ?? null;
        }
    }
    return $data;
}


function delallUserdata( $en_user_id ) {
    try {
        $redis = Redis::connection();
        $redis->del('data'.$en_user_id.'' );
        $redis->del('encrypted'.$en_user_id.'' );
    } catch (Predis\Connection\ConnectionException $ex) {
        return false;
    }
    return true;
}

function getAllActiveSchool() {
    try {
        $redis = Redis::connection();
        $list = $redis->get( 'list_of_active_schools' );
        if( $list ) {
            Log::info("Optimization fetching dat school list from redis");
            return json_decode($list);
        }
        return [];
    } catch (Predis\Connection\ConnectionException $e) {
        return [];
    }
}

function setAllActiveSchool($list) {
    try {
        $redis = Redis::connection();
        $redis->set('list_of_active_schools', json_encode($list) );
        $redis->expire('list_of_active_schools', 4320 );
        Log::info("Optimization saving dat school list to redis");
    } catch (Predis\Connection\ConnectionException $e) {
        return false;
    }
}


function setAllSchoolList($character, $list) {
    try {
        $redis = Redis::connection();
        $redis->set("school_list_$character", json_encode($list) );
        $redis->expire("school_list_$character", 4320 );
        Log::info("Optimization saving global school list $character to redis");
    } catch (Predis\Connection\ConnectionException $e) {
        return false;
    }
}

function getAllSchoolList( $character) {
    try {
        $redis = Redis::connection();
        $data = $redis->get("school_list_$character");
        if( $data ) {
            Log::info("Optimization fetching all school from the global list $character from redis");
            return json_decode($data);
        }
        return [];
    } catch (Predis\Connection\ConnectionException $e) {
        return [];
    }
}

function getAllStudentShared( $destination_school_id )  {
    try {
        $redis = Redis::connection();
        $data = $redis->get("data_shared_$destination_school_id");
        if( $data ) {
            Log::info("Get list of shared data from redis");
            return json_decode($data);
        }
        return [];
    } catch (Predis\Connection\ConnectionException $e) {
        return [];
    }
}

function setAllStudentShared($destination_school_id, $list) {
    try {
        $redis = Redis::connection();
        $redis->set("data_shared_$destination_school_id", json_encode($list) );
        $redis->expire("data_shared_$destination_school_id", 4320 );
        Log::info("Saving list of shared data to redis");
    } catch (Predis\Connection\ConnectionException $e) {
        return false;
    }
}

function delAllStudentShared($destination_school_id) {
    try {
        $redis = Redis::connection();
        $redis->del("data_shared_$destination_school_id");
        Log::info("Deleted list of shared data in redis");
    } catch (Predis\Connection\ConnectionException $e) {
        return false;
    }
}