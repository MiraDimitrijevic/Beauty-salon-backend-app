<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Offer;
use App\Models\Service;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentItemResource;
use Illuminate\Support\Facades\Auth;


class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        switch($user->userType) {
            case 'manager': 
                return ['appointments'=> AppointmentResource::collection(Appointment::where('canceled', false)->orderBy('date','asc')->orderBy('start_time','asc')->get())];
            case 'client':
                return ['appointments'=> AppointmentResource::collection(Appointment::where('canceled', false)->where('user_id', $user->id)->orderBy('date','asc')->orderBy('start_time','asc')->get())];
            case 'employee':
                return ['appointments'=> AppointmentItemResource::collection(AppointmentItem::join('offers', 'appointment_items.offer_id', '=', 'offers.id')
                ->join('appointments', 'appointment_items.appointment_id', '=', 'appointments.id')
                ->where('offers.user_id', $user->id)->orderBy('appointments.date','asc')->orderBy('appointment_items.start_time', 'asc')->get())];

        }
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
        //Bilo bi dobro da klijent salje samo jedno vreme pocetka, za prvu uslugu
        //Pazi, moze da se zakaze i kad je ned_time manji!!!
        $validator = Validator::make($request->all() , [
            'date'=>'required|date_format:Y-m-d|after:today',
            'items'=>'required'
        ]);
        if ($validator->fails())
        return response()->json($validator->errors());
        if (count($request->items) === 0) {
            return response()->json(['success'=>false,'message'=>'Appointment must have at least one item!']);
        }
        $date=$request->date;
        $costSum=0;
        $appointmentStart='23:59';
        $appointmentEnd='00:00';
        foreach($request->items as $item ){
            $validator = Validator::make($item , [
                'user_id'=>'required',
                'service_id'=>'required',
                'start_time'=>'required|date_format:H:i',
            ]);
               if ($validator->fails())
                return response()->json($validator->errors());
                $user_id= $item["user_id"];
                $employee=User::find($user_id);
                $schedule = Schedule::where('date', $date)->where('user_id', $user_id)->firstOr(function () {
                    return null;
                });
                if($schedule==null){
                    return response()->json(['success'=>false,'message'=>'Employee '. $employee->name . ' does not work that day! Try with different date!']);
                } 
                $service=Service::find($item["service_id"]);
                if(is_null($service)){
                    return response()->json(['success'=>false,'message'=>'Service does not exist!'],404);

                }
                $item["end_time"] = Carbon::parse($item["start_time"])->addMinutes($service->duration)->format('H:i');
                $scheduleStart = Carbon::parse($schedule->start_time);
                $scheduleEnd = Carbon::parse($schedule->end_time);
                $itemStartCarbon = Carbon::createFromFormat('H:i', $item["start_time"]);
                $itemEndCarbon = Carbon::createFromFormat('H:i', $item["end_time"]);
            
                if ($scheduleStart->gt($itemStartCarbon)) {
                    return response()->json(['success'=>false,'message'=>'Employee '. $employee->name . ' does not work that early! Try with different time!']);
                }
                if ($scheduleEnd->lt($itemEndCarbon)) {
                    return response()->json(['success'=>false,'message'=>'Employee '. $employee->name . ' does not work that late! Try with different time!']);
                }
                $appointmentItem = AppointmentItem::with(['offer', 'appointment'])
                ->whereHas('offer', function ($query) use ($user_id) {
                    $query->where('user_id', '=', $user_id);
                })
                ->whereHas('appointment', function ($query) use ($date) {
                    $query->where('date', '=', $date);
                })
                ->where(function ($query) use ($item) {
                    $query->where(DB::raw('appointment_items.start_time'), '>=', $item["start_time"])
                          ->where(DB::raw('appointment_items.start_time'), '<', $item["end_time"])
                          ->orWhere(function ($query) use ($item) {
                              $query->where(DB::raw('appointment_items.start_time'), '<=', $item["start_time"])
                                    ->where(DB::raw('appointment_items.end_time'), '>', $item["start_time"]);
                          });
                })
                ->get();
                if(count($appointmentItem) != 0) {
                    return response()->json(['success'=>false,'message'=>'Employee already has an appointment at ' . $appointmentItem[0]->start_time . ' . Try with different time!']);
                }
                $offer = Offer::where('service_id', $item["service_id"])->where('user_id', $item["user_id"])->firstOr(function () {
                    return null;
                });
                if($offer==null){
return response()->json(['success'=>false,'message'=>'Employee ' . $employee->name . ' does not provide ' . $service->name . '! Try with different employee!']);
                }
                $appointmentStartCarbon = Carbon::createFromFormat('H:i', $appointmentStart);
                $appointmentEndCarbon = Carbon::createFromFormat('H:i', $appointmentEnd);
                if ($appointmentStartCarbon->gt($itemStartCarbon)) {
                    $appointmentStart = $item["start_time"];
                }
                if ($appointmentEndCarbon->lt($itemEndCarbon)) {
                    $appointmentEnd = $item["end_time"];
                }
                $costSum+=$service->cost;

            }
            $user = Auth::user();
          $appointment = Appointment::create([
           'user_id'=>$user->id,
           'date'=>$request->date,
           'start_time'=>$appointmentStart,
           'end_time'=>$appointmentEnd,
           'cost'=>$costSum,
           'canceled'=>false,
          ]);
          foreach($request->items as $item ){
            $offer = Offer::where('service_id', $item["service_id"])->where('user_id', $item["user_id"])->firstOr(function () {
                return null;
            });
            $service=Service::find($item["service_id"]);
            $item["end_time"] = Carbon::parse($item["start_time"])->addMinutes($service->duration)->format('H:i');
            AppointmentItem::create([
                'offer_id'=>$offer->id,
                'appointment_id'=>$appointment->id,
                'start_time'=>$item["start_time"],
                'end_time'=>$item["end_time"]
            ]);
          }
          return response()->json(['success'=>true,'message'=>'Appointment has been successfully scheduled!', 'appointment'=>$appointment], 201);
            
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        return new AppointmentResource($appointment);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        $user = Auth::user();
        if(!$this->isMyAppointment($appointment->id, $user->id)){
            return response()->json('You do not have permition to modify those data.',401);
        }
        $validator = Validator::make($request->all() , [
            'canceled'=>'required',
        ]);
        if ($validator->fails())
        return response()->json($validator->errors());
    if($appointment->canceled==true){
        return response()->json(['success'=>true,'message'=>'Appointment is already canceled!']);
    }
    if($request->canceled == true){
        $appointment->canceled = true;
        $appointment->appointmentItem()->delete();
        $appointment->cancellation_reason = $request->cancellation_reason;
        $appointment->save();
        return response()->json(['success'=>true,'message'=>'Appointment has been successfully canceled!']);
    }
    else {
        return response()->json(['success'=>true,'message'=>'Appointment has not been canceled!']);
    }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $appointment_id)
    {
        $user = Auth::user();
        if(!$this->isMyAppointment($appointment_id, $user->id)){
            return response()->json('You do not have permition to delete those data.',401);
        }
        $appointment=Appointment::find($appointment_id);
        if(is_null($appointment)){
            return response()->json('There is no such an appointment!',404);
        }
        $appointment->appointmentItem()->delete();
        $appointment->delete();
        return response()->json(['success'=>true, 'message'=>'Appointment has been successfully deleted!']);
    }
    
}
