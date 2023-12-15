<?php
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['statamic.cp.authenticated'], 'namespace' => 'AltDesign\AltAkismet\Http\Controllers'], function() {
    // Settings
    Route::get('/alt-design/alt-akismet/', 'AltAkismetController@index')->name('alt-akismet.index');
    Route::post('/alt-design/alt-akismet/update', 'AltAkismetController@update')->name('alt-akismet.update');

    Route::get('/alt-design/alt-akismet/submissions/{submission}', 'AltAkismetController@submission')->name('alt-akismet.submission');
});
