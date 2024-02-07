<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;  // untuk validasi data
use Tymon\JWTAuth\Facades\JWTAuth;  // untuk mendapatkan token JWT
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

     /**
     * Check if the user's JWT token is still valid.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTokenValidity(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // Token is valid
            return response()->json(['valid' => true]);
        } catch (\Exception $e) {
            // Token is not valid
            return response()->json(['valid' => false]);
        }
    }

    public function index(Request $request){
        // set validasi
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // respon error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // get (ambil) "email" dan "password" dari input
        $credentials = $request->only('email', 'password');
 
        // cek jika "email" dan "password" TIDAK SESUAI
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            // respon jika login failed dikarenakan email atau password salah
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect.',
            ], 400);
        }

        // respon jika login success dengan generate token
        return response()->json([
            'success' => true,
            'user' => auth()->guard('api')->user()->only(['name', 'email']),
            'roles' => auth()->guard('api')->user()->roles->pluck('name'),
            'permissions' => auth()->guard('api')->user()->getPermissionArray(),
            'token' => $token,
        ], 200);
    }

    public function logout(){
        // remove token JWT
        JWTAuth::invalidate(JWTAuth::getToken());

        // response success logout
        return response()->json([
            'success' => true,
        ], 200);
    }
}