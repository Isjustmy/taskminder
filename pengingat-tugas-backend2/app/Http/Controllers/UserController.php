<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentClass;
use App\Models\StudentTasks;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UserController extends Controller
{
    public function getClassAndSubjects(Request $request)
    {
        try {
            // Mendapatkan data kelas
            $classes = StudentClass::all();

            // Mendapatkan data mata pelajaran guru dari definisi enum di database
            $columnDetails = DB::select("
            SELECT COLUMN_TYPE 
            FROM information_schema.columns 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [config('database.connections.mysql.database'), 'users', 'guru_mata_pelajaran'])[0];

            preg_match('/^enum\((.*)\)$/', $columnDetails->COLUMN_TYPE, $matches);
            $subjects = array_map(fn ($value) => trim($value, "'"), explode(',', $matches[1]));

            return response()->json([
                'success' => true,
                'data' => [
                    'classes' => $classes,
                    'subjects' => $subjects,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data kelas dan mata pelajaran.',
                'error' => $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    /**
     * Display a listing of users
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get users with studentClass and guru_mata_pelajaran
        $users = User::when(request()->search, function ($query) {
            $query->where('name', 'like', '%' . request()->search . '%');
        })->with(['roles', 'studentClass:id,class'])->select(['id', 'nomor_absen', 'name', 'email', 'phone_number', 'student_class_id', 'guru_mata_pelajaran'])->latest()->paginate(5);

        // Append query string to pagination links
        $users->appends(['search' => request()->search]);

        // Return with Api Resource
        return new UserResource(true, 'List Data Users', $users);
    }


    /**
     * Display the spesified user
     * 
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // get user with roles and studentClass relations
        $user = User::with(['roles', 'studentClass:id,class'])->whereId($id)->first();

        if ($user) {
            // return success with Api Resource
            return new UserResource(true, 'Detail Data User', $user);
        }

        // return failed with Api Resource
        return new UserResource(false, 'Detail Data User Tidak Ditemukan!', null);
    }

    /**
     * Display the details of the currently authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function currentUser()
    {
        // Get the currently authenticated user
        $currentUser = auth()->user();

        // If the user is authenticated, return the details
        if ($currentUser) {
            // Get user details with roles and studentClass relations
            $userDetails = User::with(['roles', 'studentClass:id,class'])->find($currentUser->id);

            // Return success with Api Resource
            return new UserResource(true, 'Detail Data User', $userDetails);
        }

        // Return failed with Api Resource
        return new UserResource(false, 'Detail Data User Tidak Ditemukan!', null);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $roles = $request->input('roles', []);

        $validator = Validator::make($request->all(), [
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|unique:users|integer' : 'nullable',
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed',
            'roles' => 'required',
            'phone_number' => 'required|numeric|unique:users,phone_number,',
            'class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|exists:student_classes,id' : 'nullable',
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? 'required|in:RPL - Produktif,Animasi - Produktif,Broadcasting - Produktif,TO - Produktif,TPFL - Produktif,Matematika,Sejarah,Pendidikan Agama,IPAS,Olahraga,Bahasa Indonesia,Bahasa Sunda,Bahasa Inggris,Bahasa Jepang' : 'nullable',
        ], [
            'nomor_absen.required' => 'Nomor Absen wajib diisi untuk peran Siswa atau Pengurus Kelas.',
            'nomor_absen.integer' => 'Nomor Absen harus berupa angka.',
            'nomor_absen.unique' => 'Nomor Absen sudah digunakan.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',

            'roles.required' => 'Peran wajib diisi.',

            'phone_number.required' => 'Nomor Telepon wajib diisi.',
            'phone_number.numeric' => 'Nomor Telepon harus berupa angka.',
            'phone_number.unique' => 'Nomor Telepon sudah digunakan.',

            'class_id.required' => 'ID Kelas wajib diisi untuk peran Siswa atau Pengurus Kelas.',
            'class_id.exists' => 'ID Kelas tidak valid.',

            'guru_mata_pelajaran.required' => 'Guru Mata Pelajaran wajib diisi untuk peran Guru.',
            'guru_mata_pelajaran.in' => 'Mata Pelajaran Guru tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Buat user
        $user = User::create([
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->nomor_absen : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
            'student_class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->class_id : null,
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? $request->guru_mata_pelajaran : null,
        ]);

        // Assign roles to user
        $user->assignRole($request->roles);

        // Automatically create tasks for the student if any
        if ($request->roles === ['siswa', 'pengurus_kelas'] && $request->class_id) {
            $this->createTasksForStudent($user->id, $request->class_id);
        }

        if ($user) {
            return new UserResource(true, 'Data User Berhasil Disimpan!', $user);
        } else {
            return new UserResource(false, 'Data User Gagal Disimpan!', null);
        }
    }


    /**
     * Automatically create tasks for the student if any.
     *
     * @param int $studentId
     * @param int $classId
     * @return void
     */
    private function createTasksForStudent($studentId, $classId)
    {
        // Check if there are tasks assigned to the class
        $tasks = Task::where('class_id', $classId)->get();

        if ($tasks->count() > 0) {
            // Assign tasks to the student
            foreach ($tasks as $task) {
                Task::create([
                    'title' => $task->title,
                    'description' => $task->description,
                    'student_id' => $studentId,
                    'deadline' => $task->deadline,
                    'creator_id' => $task->creator_id,
                    'created_at' => $task->created_at,
                ]);
            }
        }
    }


    /**
     * Update the specified resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $roles = $request->input('roles', []);

        $validator = Validator::make($request->all(), [
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|integer' : 'nullable',
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed',
            'roles' => 'required',
            'phone_number' => 'required|numeric',
            'class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|exists:student_classes,id' : 'nullable',
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? 'required|in:RPL - Produktif,Animasi - Produktif,Broadcasting - Produktif,TO - Produktif,TPFL - Produktif,Matematika,Sejarah,Pendidikan Agama,IPAS,Olahraga,Bahasa Indonesia,Bahasa Sunda,Bahasa Inggris,Bahasa Jepang' : 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Periksa apakah user yang sedang login adalah admin
        $loggedInUser = auth()->user();

        if ($loggedInUser->hasRole('admin')) {
            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'phone_number' => $request->phone_number,
                'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->nomor_absen : null,
                'student_class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->class_id : null,
                'guru_mata_pelajaran' => (in_array('guru', $roles)) ? $request->guru_mata_pelajaran : null,
            ]);

            // Sync roles for the user
            $user->syncRoles($request->roles);

            // Check if permissions are provided and sync them
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            // Return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        } elseif ($loggedInUser->id === $user->id) {
            // Update user data tanpa mengubah role
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'phone_number' => $request->phone_number,
                'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->nomor_absen : null,
                'student_class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->class_id : null,
                'guru_mata_pelajaran' => (in_array('guru', $roles)) ? $request->guru_mata_pelajaran : null,
            ]);

            // Return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        } else {
            // User yang sedang login bukan admin dan bukan user yang bersangkutan
            return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan pembaruan ini.'], 403);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            // Jika pengguna tidak ditemukan, return error
            return new UserResource(false, 'Data User Tidak Ditemukan!', null);
        }

        // Delete related tasks
        StudentTasks::where('student_id', $user->id)->delete();

        // Delete the user
        if ($user->delete()) {
            // Return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Dihapus!', null);
        }

        // Return failed with Api Resource
        return new UserResource(false, 'Data User Gagal Dihapus!', null);
    }
}
