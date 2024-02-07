<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Exclude login and register routes from auth:api middleware
Route::post('/login', [App\Http\Controllers\Api\Auth\LoginController::class, 'index']);

Route::post('/register', [App\Http\Controllers\UserController::class, 'store']);

Route::get('/getData', [App\Http\Controllers\UserController::class, 'getClassAndSubjects']);

Route::get('/check-token-validity', [LoginController::class, 'checkTokenValidity']);

// Apply auth:api middleware to all routes
Route::middleware('auth:api')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::post('/logout', [App\Http\Controllers\Api\Auth\LoginController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(['permission:users.view', 'role:admin']);
        Route::get('/current', [UserController::class, 'currentUser'])->middleware('permission:users.view');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
        Route::post('/create', [UserController::class, 'store'])->middleware('permission:users.store');
        Route::put('/{id}/update', [UserController::class, 'update'])->middleware('permission:users.edit');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
    });

    Route::prefix('tasks')->group(function () {
        Route::get('/all', [TaskController::class, 'all'])->middleware(['permission:tasks.view', 'role:admin']);
        Route::get('/', [TaskController::class, 'index'])->middleware('permission:tasks.view');
        Route::get('/{id}', [TaskController::class, 'show'])->middleware('permission:tasks.view');
        Route::post('/create', [TaskController::class, 'createTaskForStudent'])->middleware('permission:tasks.create');
        Route::post('/create/class', [TaskController::class, 'createTaskForClass'])->middleware('permission:tasks.create');
        Route::put('/{id}/submit', [TaskController::class, 'submitTaskByStudent'])->middleware('permission:tasks.submit');
        Route::put('/{id}/grade', [TaskController::class, 'gradeTaskByTeacher'])->middleware('permission:grade_task');
        Route::put('/{id}/update', [TaskController::class, 'update'])->middleware('permission:tasks.edit');
        Route::delete('/deleteTask/{id}', [TaskController::class, 'deleteTaskFromTeacher'])->middleware('permission:tasks.delete');
        Route::delete('/deleteStudentTask/{id}', [TaskController::class, 'deleteTaskFromTeacher'])->middleware('permission:tasks.delete');
    });

    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->middleware('permission:personal_task_calendar');
        Route::get('/{id}', [CalendarController::class, 'show'])->middleware('permission:personal_task_calendar');
        Route::post('/create', [CalendarController::class, 'store'])->middleware('permission:personal_task_calendar');
        Route::put('/{id}/update', [CalendarController::class, 'update'])->middleware('permission:personal_task_calendar');
        Route::delete('/{id}', [CalendarController::class, 'delete'])->middleware('permission:personal_task_calendar');
    });

    // Endpoint untuk mendapatkan notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Endpoint untuk menandai notifikasi sebagai sudah dibaca
    Route::post('/notifications/mark-as-read/{notification}', [NotificationController::class, 'markAsRead']);
});
