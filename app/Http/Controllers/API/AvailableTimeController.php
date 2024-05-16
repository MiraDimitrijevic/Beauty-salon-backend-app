<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppointmentItem;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AvailableTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $service_id, int $user_id, string $date)
    {


       $filteredTimes = [];

        $validator = Validator::make([
            'date' => $date,], [
            'date'=>'required|date_format:Y-m-d|after:today'
                    ]);
                if ($validator->fails())
                return response()->json($validator->errors());

        $schedule = Schedule::where('date', $date)->where('user_id', $user_id)->firstOr(function () {
            return null;
        });

        if($schedule){
            $service= Service::where('id',$service_id)->firstOr(function () {
                return null;
            });
            if($service==null){
                return response()->json(['success' => false,'message'=>'Service does not exists!']);
            }
            $start_time=$schedule->start_time;
            $end_time=$schedule->end_time;
            $times= $this->generateTimeArray($start_time, $end_time);
            $filteredTimes = array_filter($times, function ($service_start) use ($user_id, $date, $service) {
                $service_end = Carbon::parse($service_start)->addMinutes($service->duration)->format('H:i');
               return $appointmentItem = AppointmentItem::with(['offer', 'appointment'])
                    ->whereHas('offer', function ($query) use ($user_id) {
                        $query->where('user_id', '=', $user_id);
                    })
                    ->whereHas('appointment', function ($query) use ($date) {
                        $query->where('date', '=', $date);
                    })
                    ->where(function ($query) use ($service_start, $service_end) {
                        $query->where(DB::raw('appointment_items.start_time'), '>=', $service_start)
                              ->where(DB::raw('appointment_items.start_time'), '<', $service_end)
                              ->orWhere(function ($subQuery) use ($service_start) {
                                  $subQuery->where(DB::raw('appointment_items.start_time'), '<=', $service_start)
                                           ->where(DB::raw('appointment_items.end_time'), '>', $service_start);
                              });
                    })
                    ->get();
            
                });
                return response()->json(['success' => true,'filteredTimes'=>$filteredTimes]);


        }
        else {    
            return response()->json(['success' => false,'message'=>'Employee does not work that day! Try a different date!']);

        }    
    }

    private function generateTimeArray($startTime, $endTime)
{
    $start = Carbon::parse($startTime);
    $end = Carbon::parse($endTime);

    $times = [];

    while ($start <= $end) {
        $times[] = $start->format('H:i');
        $start->addMinutes(15);
    }

    return $times;
}

public function checkAvailability($start_time, $end_time)
{
    // Retrieve existing AppointmentItems that overlap with the desired schedule
    $existingAppointments = AppointmentItem::where(function ($query) use ($start_time, $end_time) {
        $query->whereBetween('start_time', [$start_time, $end_time])
            ->orWhereBetween('end_time', [$start_time, $end_time])
            ->orWhere(function ($query) use ($start_time, $end_time) {
                $query->where('start_time', '<', $start_time)
                    ->where('end_time', '>', $end_time);
            });
    })->get();

    // Check if the start_time is after the end_time of any existing AppointmentItem
    $isStartTimeAvailable = $existingAppointments->every(function ($appointment) use ($start_time) {
        return strtotime($start_time) >= strtotime($appointment->end_time);
    });

    // Check if the end_time is before the start_time of any existing AppointmentItem
    $isEndTimeAvailable = $existingAppointments->every(function ($appointment) use ($end_time) {
        return strtotime($end_time) <= strtotime($appointment->start_time);
    });

    // Now, $isStartTimeAvailable and $isEndTimeAvailable indicate availability

    // ... (your logic based on availability)

    return response()->json(['isStartTimeAvailable' => $isStartTimeAvailable, 'isEndTimeAvailable' => $isEndTimeAvailable]);
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AvailableTime  $availableTime
     * @return \Illuminate\Http\Response
     */
    public function show(AvailableTime $availableTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AvailableTime  $availableTime
     * @return \Illuminate\Http\Response
     */
    public function edit(AvailableTime $availableTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AvailableTime  $availableTime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AvailableTime $availableTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AvailableTime  $availableTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(AvailableTime $availableTime)
    {
        //
    }
}
