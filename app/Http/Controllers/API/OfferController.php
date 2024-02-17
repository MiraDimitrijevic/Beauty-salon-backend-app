<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Offer;
use App\Models\Employee;
use App\Models\Service;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $service_id = $request->service_id;
    
        $offers = Offer::with('employee')->where('service_id', $service_id)->get();
        if ($offers->isEmpty()) {
            return response()->json("There are no offers for this service.", 404);
        }

        $employeeResources = $offers->map(function ($offer) {
            $employee = $offer->relationLoaded('employee') ? $offer->employee : null;
            if($employee!=null)
            return new EmployeeResource($employee);
        });
    
        return response()->json(['employees' => $employeeResources, 'service' => $service_id]);
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
        $offer= Offer::where('user_id', $request->user_id)->where('service_id', $request->service_id)->get();
        if(count($offer)!=0) {
            return response()->json(['success'=>false, 'message'=>'Offer already exists!']);
        } else {
            $service=Service::find($request->service_id);
            if(is_null($service)){
                return response()->json('Service does not exist.',404);
            }
            $employee = Employee::where('user_id', $request->user_id)->firstOr(function () {
                return null;
            });
    
            if(is_null($employee)){
                return response()->json('Employee does not exist.',404);
            }
        $offer= Offer::create ([
         'service_id'=>$request->service_id,
         'user_id'=>$request->user_id
        ]);
        return response()->json(['success'=>true, 'message'=>'Offer is successfully created!']);
    }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $offer_id)
    {
        $offer=Offer::find($offer_id);
        if(is_null($offer)){
            return response()->json('Offer does not exist.',404);
        }
        $offer->delete();
        return response()->json(['success'=>true, 'message'=>'Offer is successfully deleted!']);
    
    }
}
