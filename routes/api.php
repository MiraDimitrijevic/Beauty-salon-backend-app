<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\OfferController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\AppointmentItemController;
use App\Http\Controllers\API\AvailableTimeController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']);


Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', function (Request $request) {
        return auth()->user();
 });

 Route::resource('service', ServiceController::class)->only(['show', 'index']);
 Route::resource('offer', OfferController::class)->only(['index']);
 Route::resource('appointment', AppointmentController::class)->only(['index', 'show']);
 Route::resource('employee', EmployeeController::class)->only(['index']);
 Route::post('/logout', [AuthController::class, 'logout']);

 Route::middleware(['user.type:manager'])->group(function () {
    Route::resource('service', ServiceController::class)->only(['update','store','destroy']);
    Route::resource('employee', EmployeeController::class)->only(['store','destroy']);
    Route::resource('offer', OfferController::class)->only(['store','destroy']);
    Route::post('/schedule', [ScheduleController::class, 'store']);
    Route::put('/schedule/{user_id}/{date}', [ScheduleController::class, 'update']);
    Route::delete('/schedule/{user_id}/{date}', [ScheduleController::class, 'destroy']);
});

Route::middleware(['user.type:client'])->group(function () {
    Route::resource('appointment', AppointmentController::class)->only(['store','update','destroy']);
    Route::resource('appointmentItem/{appointment_id}', AppointmentItemController::class)->only(['index','store']);
    Route::get('/availableTime/{service_id}/{user_id}/{date}', [AvailableTimeController::class, 'index']);

});



});
