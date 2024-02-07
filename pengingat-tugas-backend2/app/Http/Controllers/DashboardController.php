<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Get authenticated user's data
        $user = Auth::user();

        // Get the roles associated with the user using Spatie Role Permission
        $roles = $user->getRoleNames();

        // Return the data as JSON
        return response()->json([
            'user' => $user,
            'roles' => $roles,
        ]);
    }
}
