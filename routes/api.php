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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', function (Request $request) {
        return auth()->user();
 });

 //zadatak: dozvoliti Employee-ju da vidi svoje i samo svoje zakazane termine, najbolje po datumu
 //Klijent da vidi sve svoje zakazane termine (posebno po datumu omoguciti)
 //Ali samo menadzeru dozvoliti da vidi sve zakazane termine ikada

 //Kreirati AppointmentItem resurs, gde ce Client moci da menja, brise i dodaje stavke svog termina (provera da li je termin njegov)
//Update i delete Appointment -> napraviti proveru da li taj termin pripada bas tom klijentu
//Revidirati metode appoitnment -> store i availableTime

//Razmotriti eventualno skladistenje metoda koje koriste multiple kontroleri negde drugde
//Dodati sortiranje, filtriranje i paginaciju kod indeks metoda sledecih:
//-service: sort po name, duration i cost. Filter po ceni i duration. Paginacija
//-appointmentItem prikaz svih: da bude redom po start-time-u, ali ne dozvoliti tu da korisnik nesto bira
//-appointment kod mng mozda sortiranje, filtriranje po datumu itd
//PROVERI DA LI SE STATUSI PODUDARAJU

//Proveriti da li aplikacija ima sve odlike REST API-ja

 Route::resource('service', ServiceController::class)->only(['show', 'index']);
 Route::resource('offer', OfferController::class)->only(['index']);
 Route::resource('appointment', AppointmentController::class)->only(['index', 'show']);
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
    Route::get('/availableTime/{service_id}/{user_id}/{date}', [AvailableTimeController::class, 'index']);
    Route::resource('appointment', AppointmentController::class)->only(['store','update','destroy']);
    Route::resource('appointmentItem/{appointment_id}', AppointmentItemController::class)->only(['index','show','store','update','destroy']);
});



});
