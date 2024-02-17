<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
        'date'=>'required|date_format:Y-m-d|after:today',
        'start_time'=>'required|date_format:H:i',
        'end_time'=>'required|date_format:H:i',
        'user_id'=>'required',
                ]);


        if ($validator->fails())
            return response()->json($validator->errors());

            $schedule = Schedule::where('date', $request->date)->where('user_id', $request->user_id)->firstOr(function () {
                return null;
            });

            if($schedule){
         return response()->json(['success' => false,'message'=>'Schedule already exists!']);

            }
            else {
                $employee = Employee::where('user_id', $request->user_id)->firstOr(function () {
                    return null;
                });
        
                if(is_null($employee)){
                    return response()->json('Employee does not exist.',404);
                }
        $schedule = Schedule::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'user_id' => $request->user_id,
        ]);


        return response()->json(['success' => true,'message'=>'Schedule has been successfully created!']);
    }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $user_id, string $date)
    {

            $validator = Validator::make($request->all(), [
                'start_time'=>'required|date_format:H:i',
                'end_time'=>'required|date_format:H:i',
                        ]);
        
        
                if ($validator->fails())
                    return response()->json($validator->errors());
                    $employee = Employee::where('user_id', $user_id)->firstOr(function () {
                        return null;
                    });
            
                    if(is_null($employee)){
                        return response()->json('Employee does not exist.',404);
                    }

                    Schedule::updateOrInsert(
                        ['user_id' => $user_id, 'date' => $date],
                        ['start_time' => $request->start_time, 'end_time' => $request->end_time]
                    );       

                return response()->json(['success' => true,'message'=>'Schedule has been successfully updated!']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule, int $user_id, string $date)
    {
        $schedule = Schedule::where('user_id', $user_id)
        ->where('date', $date)
        ->first();
    if ($schedule) {
        $schedule->where('user_id', $user_id)
        ->where('date', $date)->delete();
        
        return response()->json(['message' => 'Schedule deleted successfully']);
    } else {
        return response()->json(['message' => 'Schedule not found'], 404);
    }
    }
}
