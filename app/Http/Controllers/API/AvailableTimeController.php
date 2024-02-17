<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AvailableTime;
use App\Models\AppointmentItem;
use App\Models\Schedule;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class AvailableTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $service_id, int $user_id, string $date)
    {
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
            $filteredTimes = array_filter($times, function ($service_start) use ($service){
                $service_end = Carbon::parse($service_start)->addMinutes($service->duration)->format('H:i');
                $appointmentItem = AppointmentItem::with(['offer', 'appointment'])
                ->whereHas('offer', function ($query) use ($user_id) {
                    $query->where('user_id', '=', $user_id);
                })
                ->whereHas('appointment', function ($query) use ($date) {
                    $query->where('date', '=', $date);
                })
                ->where('start_time', '<=', $service_start)
                ->where('end_time', '>=', $service_end)
                ->get();
                //врати уколико нијe nadjen takav appointmentItem i revidiraj uslove za start i end time
                //proveri i da li je duzina trajanja usluge takva da premasuje kraj radnog vremena
                //verovatno ces brisati appoitnment model i migraciju itd
                //sutra middleware-i i ovo ako stignes
                //prekosutra sortiranje i paginacija za USLUGE, zaposlene kod OFFER-a, appoitnment-iteme po danu
                //mozda i neki filter za cenu itd
                //napraviti i kontroler kojim se prikazuju zakazanitermini za svakog zaposlenog za taj dan
                return strtotime($service_start) >= strtotime('15:00');
            });


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
