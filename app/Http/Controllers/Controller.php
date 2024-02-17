<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Schedule;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    //here will be just methods that cannot return response()->json(), just some accountings
    protected function isMyAppointment (int $appointment_id, int $client_id){
        $client = Client::where('user_id', $client_id)->firstOr(function () {
            return null;
        });

        if(is_null($client)){
            return response()->json('Client does not exist.',404);
        }
        $appointment = Appointment::find($appointment_id);
        if(is_null($client)){
            return response()->json('Appointment does not exist.',404);
        }
        if($appointment->user_id == $client_id) return true;
        else return false;
    }
}
