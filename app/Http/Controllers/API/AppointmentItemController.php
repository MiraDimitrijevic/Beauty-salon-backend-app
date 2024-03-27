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


class AppointmentItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $appointment_id)
    {
        $user = Auth::user();
        if(!$this->isMyAppointment($appointment_id, $user->id)){
            return response()->json('You do not have permition to see those data.',401);
        }
        $items=AppointmentItem::get()->where('appointment_id', $appointment_id);
        if(is_null($items)) return response()->json("There is no items for this appointment!",404);
        else return response()->json( ['items' => AppointmentItemResource::collection($items)]);
           
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
    public function store(Request $request, int $appointment_id)
    {
        $user = Auth::user();
        if(!$this->isMyAppointment($appointment_id, $user->id)){
            return response()->json('You do not have permition to modify those data.',401);
        }
            $validator = Validator::make($request->all() , [
                'user_id'=>'required',
                'service_id'=>'required',
                'start_time'=>'required|date_format:H:i',
            ]);
               if ($validator->fails())
               return response()->json(['error'=>$validator->errors(), 'success'=>false]);
               $appointment = Appointment::find($appointment_id);
               if($appointment->canceled == true){
                return response()->json(['success'=>false,'message'=>'Appointment is canceled!']);
               }
               $user_id=$request->user_id;
               $employee=User::find($user_id);
               $service=Service::find($request->service_id);
               if(is_null($service)){
                return response()->json(['success'=>false,'message'=>'Service does not exist!'],404);
            }
               $schedule = Schedule::where('date', $appointment->date)->where('user_id', $user_id)->firstOr(function () {
                return null;
            });
            $endTime = Carbon::parse($request->start_time)->addMinutes($service->duration)->format('H:i');
            $scheduleStart = Carbon::parse($schedule->start_time);
            $scheduleEnd = Carbon::parse($schedule->end_time);
            $itemStartCarbon = Carbon::createFromFormat('H:i', $request->start_time);
            $itemEndCarbon = Carbon::createFromFormat('H:i', $endTime);
        
            if ($scheduleStart->gt($itemStartCarbon)) {
                return response()->json(['success'=>false,'message'=>'Employee '. $employee->name . ' does not work that early! Try with different time!']);
            }
            if ($scheduleEnd->lt($itemEndCarbon)) {
                return response()->json(['success'=>false,'message'=>'Employee '. $employee->name . ' does not work that late! Try with different time!']);
            }
            
            $appointmentItems = AppointmentItem::with(['offer', 'appointment'])
            ->whereHas('offer', function ($query) use ($user_id) {
                $query->where('user_id', '=', $user_id);
            })
            ->whereHas('appointment', function ($query) use ($appointment) {
                $query->where('date', '=', $appointment->date);
            })->where(function ($query) use ($request, $endTime) {
                $query->where(DB::raw('appointment_items.start_time'), '>=', $request->start_time)
                      ->where(DB::raw('appointment_items.start_time'), '<', $endTime)
                      ->orWhere(function ($query) use ($request) {
                          $query->where(DB::raw('appointment_items.start_time'), '<=', $request->start_time)
                                ->where(DB::raw('appointment_items.end_time'), '>', $request->start_time);
                      });
            })->get();
            if(count($appointmentItems) != 0) {
                return response()->json(['success'=>false,'message'=>'Employee already has an appointment at ' . $appointmentItems[0]->start_time . ' . Try with different time!']);
            }
            $offer = Offer::where('service_id', $service->id)->where('user_id', $user_id)->firstOr(function () {
                return null;
            });
            if($offer==null){
return response()->json(['success'=>false,'message'=>'Employee ' . $employee->name . ' does not provide ' . $service->name . '! Try with different employee!']);
            }
            $appointmentStartCarbon = Carbon::createFromFormat('H:i:s', $appointment->start_time);
            $appointmentEndCarbon = Carbon::createFromFormat('H:i:s', $appointment->end_time);
            if ($appointmentStartCarbon->gt($itemStartCarbon)) {
                $appointment->start_time = $request->start_time;
            }
            if ($appointmentEndCarbon->lt($itemEndCarbon)) {
                $appointment->end_time = $endTime;
            }
            $appointment->cost+=$service->cost;
            AppointmentItem::create([
                'offer_id'=>$offer->id,
                'appointment_id'=>$appointment->id,
                'start_time'=>$request->start_time,
                'end_time'=>$endTime
            ]);
            $appointment->save();
            return response()->json(['success'=>true,'message'=>'Appointment Item has been successfully added!'], 201);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AppointmentItem  $appointmentItem
     * @return \Illuminate\Http\Response
     */
    public function show(AppointmentItem $appointmentItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AppointmentItem  $appointmentItem
     * @return \Illuminate\Http\Response
     */
    public function edit(AppointmentItem $appointmentItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AppointmentItem  $appointmentItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AppointmentItem $appointmentItem)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AppointmentItem  $appointmentItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $appointment_id, int $appointmentItem_id)
    {
        
    }
    


}
