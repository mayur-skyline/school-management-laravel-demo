<?php


Route::group(['middleware' => ['connection', 'auth:authorizedusers']], function () {

    Route::get('/session-data', 'AstNext\AstNextController@sessionData');
});
