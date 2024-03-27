<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Client;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','refresh' ]]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5',
            'email' => 'required|string|min:5|email|unique:users',
            'password'=> array ('required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/' , 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'),
            'contact' => 'required|string|starts_with:+,06|min:9'
        ]);


        if ($validator->fails())
        return response()->json(['error' => $validator->errors(), 'success' => false]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $client = Client::create ([
            'user_id'=>$user->id,
            'contact'=>$request->contact
        ]);

        $token = Auth::login($user);

        return response()->json(['success' => true, 'access_token' => $token, 'token_type' => 'Bearer', 'id'=>$user->id]);
    }



    public function login(Request $request)
    {  $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        return ['message' => 'You have successfully logged off.'];
    }
    
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}