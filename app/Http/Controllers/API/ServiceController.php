<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;


class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $max_price = $request->input('max_price');
        $min_price = $request->input('min_price');
        $name = $request->input('name');
        if(is_null($max_price) || is_null($min_price)){
            return response()->json(['message' => 'All parameters are required'], 400);
        }
        if(is_null($name)) $name="";
        $services = Service::where('cost', '>=', $min_price)->where('cost', '<=', $max_price)->where('name', 'like', $name . '%');
        $sort = $request->input('sort');
        if(!is_null($sort)){
        $sortColumns = explode(',', $sort);
        foreach ($sortColumns as $sortColumn) {
            [$column, $direction] = explode('|', $sortColumn);
            $column = strtolower(trim($column));
            $direction = strtolower(trim($direction));
            if(($column == 'cost' || $column =='duration' || $column =='name' || $column =='description' || $column =='id') 
            && ($direction=='asc' || $direction=='desc'))
            $services->orderBy($column, $direction);
        }
    }
        $services = $services->get();
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $services = new LengthAwarePaginator(
            $services->forPage($page, $perPage),
            $services->count(),
            $perPage,
            $page
        );

        return ['services'=> ServiceResource::collection($services)];
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
        
        $validator = Validator::make($request->all() , [
            'name'=> 'string|required|max:50|unique:services,name',
            'cost'=>'required|numeric|min:0|max:30000',
            'description'=>'string|max:500',
            'duration'=>'required|numeric|min:5|max:300' 
        ]);

        
        if ($validator->fails())
            return response()->json($validator->errors());

            $service=Service::create([
             'name'=>$request->name,
             'cost'=>$request->cost,
             'description'=>$request->description,
             'duration'=>$request->duration,
            ]);

            return response()->json(['success'=>true,'service'=> new ServiceResource($service)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function show(Service $service)
    {
        return new ServiceResource($service);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Service $service)
    {
        $validator = Validator::make($request->all() , [
            'name'=> 'string|required|max:50',
            'cost'=>'required|numeric|min:0|max:30000',
            'description'=>'string|max:500',
            'duration'=>'required|numeric|min:5|max:300' 
        ]);

        
        if ($validator->fails())
            return response()->json($validator->errors());

            $service->name=$request->name;
            $service->cost=$request->cost;
            $service->description=$request->description;
            $service->duration=$request->duration;
            $service->save();
            return response()->json(['success'=>true,'service'=> new ServiceResource($service)]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $service_id)
    {
        $service=Service::find($service_id);
        if(is_null($service)){
            return response()->json('Service does not exist.',404);
        }
        $service->delete();
        return response()->json(['success'=>true, 'message'=>'The service has been successfully removed from the offer.']);
    }
}
