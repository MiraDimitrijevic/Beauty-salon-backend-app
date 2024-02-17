<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppointmentItem;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AppointmentItemResource;

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
                return response()->json($validator->errors());
        $appointment = Appointment::find($appointment_id);
        //redefine AppointmentController method first, transfer some code partitions to Controller and then write those methods
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
        $user = Auth::user();
        if(!isMyAppointment($appointment_id, $user->id)){
            return response()->json('You do not have permition to modify those data.',401);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AppointmentItem  $appointmentItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(AppointmentItem $appointmentItem)
    {
        //
    }


}
