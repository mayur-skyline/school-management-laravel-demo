<?php

use Illuminate\Support\Facades\Route;

//CSV Import
//Route::any('/*', 'SetUp\ExternalServiceController@ExternalService');
Route::group(['middleware' => ['connection', 'auth:authorizedusers', 'apiResponseType']], function () {
    Route::any('/{any}', 'SetUp\ExternalServiceController@ExternalService')->where('any', '.*');
});
