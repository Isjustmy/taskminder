<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\IdentifierController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckClassManagerRole;
use App\Http\Middleware\CheckStudentOrClassManagerRole;
use App\Http\Middleware\CheckTeacherRole;

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
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'index']);

Route::post('/register', [App\Http\Controllers\UserController::class, 'store']);

Route::get('/getData', [App\Http\Controllers\UserController::class, 'getClassAndSubjects']);

Route::post('password/email', [UserController::class, 'sendResetLink'])->name('password.email');

Route::post('password/reset', [UserController::class, 'resetPassword'])->name('password.reset');

Route::get('/rekapitulasi/{teacherId}/{idKelas}', [TaskController::class, 'exportTaskScore']);

Route::post('/import/nisn', [IdentifierController::class, 'importNISN']);
Route::post('/import/nip', [IdentifierController::class, 'importNIP']);

// Apply auth:api middleware to all routes
Route::middleware('auth:api')->group(function () {

    Route::get('/getTeacherData',[UserController::class, 'getTeacherData'])->middleware('role:admin|pengurus_kelas|guru');

    Route::get('/tesRole', [TaskController::class, 'tesGetRole']);

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(['permission:users.view', 'role:admin']);
        Route::get('/current', [UserController::class, 'currentUser']);
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
        Route::post('/create', [UserController::class, 'store'])->middleware('permission:users.store');
        Route::put('/{id}/update', [UserController::class, 'update'])->middleware('permission:users.edit');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:users.delete');

        Route::post('/storeTokenFcm', [UserController::class, 'storeUserTokenFcm']);
    });


    Route::prefix('tasks')->group(function () {
        Route::get('/murid', [TaskController::class, 'tugasSiswa'])->middleware([CheckStudentOrClassManagerRole::class]);
        Route::get('/murid/{id}', [TaskController::class, 'tugasSiswaDenganId'])->middleware([CheckStudentOrClassManagerRole::class]);

        Route::get('/class', [TaskController::class, 'tugasKelasKhusus'])->middleware([CheckClassManagerRole::class]);
        Route::post('/class/id', [TaskController::class, 'tugasPerKelas'])->middleware([CheckAdminRole::class], [CheckTeacherRole::class]);

        // grup pengambilan data tugas dengan referensi function berbeda
        Route::get('/all', [TaskController::class, 'all'])->middleware(['permission:tasks.view', 'role:admin']);
        Route::get('/{id}', [TaskController::class, 'show'])->middleware(['permission:tasks.view', 'role:admin']);
        Route::get('/list/teacher', [TaskController::class, 'getTeacherTasks'])->middleware('permission:tasks.view');
        Route::get('/list/teacher/{id}', [TaskController::class, 'getTeacherTasksWithId'])->middleware('permission:tasks.view');
        Route::get('/list/summary', [TaskController::class, 'taskSummary'])->middleware('permission:tasks.view', 'role:guru');
        Route::get('/list/summary/{id}', [TaskController::class, 'taskSummaryWithId'])->middleware('permission:tasks.view', 'role:guru');
        Route::post('/detail/submit_student', [TaskController::class, 'getTaskAndSubmissionData'])->middleware('permission:tasks.view', 'role:guru');


        // Route::post('/create', [TaskController::class, 'createTaskForStudent'])->middleware('permission:tasks.create');
        Route::post('/create/class', [TaskController::class, 'createTaskForClass'])->middleware('permission:tasks.create');
        Route::post('/{id}/submit', [TaskController::class, 'submitTaskByStudent'])->middleware('permission:tasks.submit');
        Route::put('/{id}/grade', [TaskController::class, 'gradeTaskByTeacher'])->middleware('permission:grade_task');
        Route::post('/{id}/update', [TaskController::class, 'update'])->middleware('permission:tasks.edit');
        Route::delete('/deleteTask/{id}', [TaskController::class, 'deleteTaskFromTeacher'])->middleware('permission:tasks.delete');
        Route::put('/resetSubmit/{id}', [TaskController::class, 'deleteTaskFromStudent'])->middleware('role:siswa|pengurus_kelas');

        //new: route get student task file
        // Route::get('/{id}/file', [TaskController::class, 'getStudentTaskFile'])->middleware('permission:tasks.view');
    });


    /**
     * Endpoint untuk CRUD data kelas yang terdaftar
     */
    Route::prefix('class')->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::post('/create', [ClassController::class, 'store'])->middleware('role:admin');
        Route::put('/{id}', [ClassController::class, 'update'])->middleware('role:admin');
        Route::delete('/{id}', [ClassController::class, 'destroy'])->middleware('role:admin');
        Route::post('/reset', [ClassController::class, 'resetData'])->middleware('role:admin');
    });


    /**
     * Endpoint data kalender untuk role siswa (dan pengurus kelas, karena pengurus kelas
     * sudah otomatis ditandai sebagai role siswa.)
     */
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->middleware('permission:personal_task_calendar');
        Route::get('/{id}', [CalendarController::class, 'show'])->middleware('permission:personal_task_calendar');
        Route::post('/create', [CalendarController::class, 'store'])->middleware('permission:personal_task_calendar');
        Route::put('/{id}/update', [CalendarController::class, 'update'])->middleware('permission:personal_task_calendar');
        Route::delete('/{id}/delete', [CalendarController::class, 'destroy'])->middleware('permission:personal_task_calendar');
    });

    
    /** 
     * Endpoint data akun user untuk role admin
     */
    Route::get('/akun/siswa/{classId}', [UserController::class, 'getSiswaUsers'])->middleware(['permission:users.view', 'role:admin']);
    Route::post('/akun/guru', [UserController::class, 'getTeacherData'])->middleware(['permission:users.view', 'role:admin']);
    Route::get('/akun/admin', [UserController::class, 'getAdminUsers'])->middleware(['permission:users.view', 'role:admin']);
    Route::get('/akun/pengurus_kelas/{idKelas}', [UserController::class, 'getPengurusKelasUsers'])->middleware(['permission:users.view', 'role:admin']);


    /**
     * Endpoint Notifikasi
     */
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);

        // tandai notifikasi telah dibaca spesifik per id notifikasi yang dikirim pada body request
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
    
        // tandai semua notifikasi telah dibaca
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    
        // hapus notifikasi per id notifikasi yang dikirim pada body request
        Route::post('/deletePerId', [NotificationController::class, 'deleteNotificationPerId']);
    
        // hapus semua notifikasi
        Route::delete('/deleteAll', [NotificationController::class, 'deleteAllNotifications']);
    });
   
});
