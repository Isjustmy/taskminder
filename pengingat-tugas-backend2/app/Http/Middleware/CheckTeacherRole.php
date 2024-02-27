<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTeacherRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah pengguna memiliki salah satu dari role 'siswa' atau 'pengurus_kelas'
        if ($request->user() && $request->user()->hasRole('guru')) {
            // Jika iya, izinkan pengguna untuk melanjutkan ke route
            return $next($request);
        }

        // Jika tidak, kembalikan response error
        return response()->json(['error' => 'Anda tidak memiliki izin untuk mengakses route ini.'], 403);
    }
}
