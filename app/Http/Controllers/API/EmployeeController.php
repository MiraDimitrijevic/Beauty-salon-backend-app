<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
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
            'name' => 'required|string|max:50',
            'email' => 'required|string|max:40|email|unique:users',
            'password'=> array ('required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/' , 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'),
            'profession'=> 'required|string|max:30'
                ]);


        if ($validator->fails())
            return response()->json($validator->errors());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'userType'=>'employee'
        ]);

        $employee = Employee::create ([
            'user_id'=>$user->id,
            'profession'=>$request->profession,
            'imageURL'=>$request->imageURL,
        ]);


        return response()->json(['success' => true, 'employee'=> new EmployeeResource($employee)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $employee_id)
    {
        $employee=User::find($employee_id);
        if(is_null($employee)){
            return response()->json('Employee does not exist.',404);
        }
        $employee->delete();
        Employee::where('user_id', $employee_id)->delete();
        return response()->json(['success'=>true, 'message'=>'Employee has been successfully deleted!']);
    
    }
}
