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

// Container liveness probe (used by the Docker HEALTHCHECK).
// Deliberately dependency-free: no piGarden socket, no database, no session.
// Probing "/" instead would report the container unhealthy whenever the
// Raspberry Pi is slow or offline, which says nothing about this container.
Route::get('/health', function () {
    return response('ok', 200)->header('Content-Type', 'text/plain');
})->withoutMiddleware(['web'])->name('health');

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
