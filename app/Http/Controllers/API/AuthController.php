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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|max:40|email|unique:users',
            'password'=> array ('required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/' , 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'),
            'contact' => 'required|string|starts_with:+,06|min:9'
        ]);


        if ($validator->fails())
            return response()->json($validator->errors());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $client = Client::create ([
            'user_id'=>$user->id,
            'contact'=>$request->contact
        ]);

        $token = $user->createToken('registration_token')->plainTextToken;

        return response()->json(['success' => true, 'access_token' => $token, 'token_type' => 'Bearer', 'user'=> new UserResource($user)]);
    }



    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()
                ->json(['message' => 'Incorect credentials!'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('login_token')->plainTextToken;

        return response()
            ->json(['message' => 'Hello, ' . $user->name . ', Welcome to home page.', 'access_token' => $token, 'token_type' => 'Bearer',]);
    }
    
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'You have successfully logged off.'
        ];
    }
    
}