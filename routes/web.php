<?php

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

// Use config() rather than env(): env() returns null once `php artisan config:cache` is run
if (config('pigarden.force_https', false)) {
    URL::forceScheme('https');
}

Route::group(['namespace' => 'PiGardenBase'], function() {
    Route::get('/', [
        'uses' => 'PiGardenPublicController@getHome',
        'as' => 'home',
    ]);

    Route::get('/jsonDashboardStatus/{extra_parameter?}', [
        'as' => 'get.json.dashboard.status',
        'uses' => 'PiGardenPublicController@getJsonDashboardStatus',
        'extra_parameter' => '(^$|get_cron_open_in)'
    ]);

});
