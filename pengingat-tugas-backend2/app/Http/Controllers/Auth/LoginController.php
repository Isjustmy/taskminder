<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
     public function index(Request $request)
    {
        // set validasi
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    // Lakukan validasi menggunakan regex untuk memastikan alamat email sesuai format
                    if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $value)) {
                        $fail('Format email tidak valid.');
                    }
                },
            ],
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

        try {
            // coba melakukan autentikasi
            if (!$token = auth()->guard('api')->attempt($credentials)) {
                // respon jika login failed dikarenakan email atau password salah
                return response()->json([
                    'success' => false,
                    'message' => 'Email or Password is incorrect.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'user' => auth()->guard('api')->user()->only(['name', 'email']),
                'roles' => auth()->guard('api')->user()->roles->pluck('name'),
                'permissions' => auth()->guard('api')->user()->getPermissionArray(),
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            // Tangani kesalahan umum
            return response()->json(['error' => 'Terjadi kesalahan. Harap coba lagi nanti.'], 500);
        }
    }

    public function logout()
    {
        // remove token JWT
        JWTAuth::invalidate(JWTAuth::getToken());

        // response success logout
        return response()->json([
            'success' => true,
        ], 200);
    }
}
