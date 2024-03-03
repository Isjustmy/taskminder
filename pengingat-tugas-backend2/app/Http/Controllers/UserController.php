<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentClass;
use App\Models\StudentIdentifier;
use App\Models\StudentTasks;
use App\Models\Task;
use App\Models\TeacherIdentifier;
use App\Notifications\TaskNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAdminUsers()
    {
        try {
            $users = User::role('admin')->paginate(5);
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data admin tidak ditemukan.',
                    'data' => []
                ]);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users->items() as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data admin berhasil diambil.',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data admin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getGuruUsers()
    {
        try {
            $users = User::role('guru')->paginate(5);
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data guru tidak ditemukan.',
                    'data' => []
                ]);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users->items() as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data guru berhasil diambil.',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data guru.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getSiswaUsers()
    {
        try {
            $users = User::role('siswa')->with('studentClass')->paginate(5);
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan.',
                    'data' => []
                ]);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users->items() as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diambil.',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data siswa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getPengurusKelasUsers()
    {
        try {
            $users = User::role('pengurus_kelas')->with('studentClass')->paginate(5);
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengurus kelas tidak ditemukan.',
                    'data' => []
                ]);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users->items() as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengurus kelas berhasil diambil.',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengurus kelas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


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

    function getTeacherData()
    {
        try {
            // Mendapatkan semua user yang memiliki peran "guru"
            $teachers = User::whereHas('roles', function ($query) {
                $query->where('name', 'guru');
            })->select('id', 'name', 'guru_mata_pelajaran')->get();

            // Memeriksa apakah ada data guru yang ditemukan
            if ($teachers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data guru tidak ditemukan.',
                ], 404);
            }

            // Mengirimkan data guru jika berhasil ditemukan
            return response()->json([
                'success' => true,
                'message' => 'Data guru berhasil ditemukan.',
                'data' => $teachers,
            ], 200);
        } catch (\Exception $e) {
            // Menangani kesalahan jika terjadi
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data guru.',
                'error' => $e->getMessage(),
            ], 500);
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
     * Display the specified user
     * 
     * @param int $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get user with roles and appropriate identifier relation
        $user = User::with(['roles', 'teacherIdentifier', 'studentIdentifier'])->whereId($id)->first();

        if ($user) {
            // Initialize an array to hold the user details
            $userData = $user->toArray();

            // Check if the user is a teacher
            if ($user->hasRole('guru')) {
                // If the user is a teacher, remove the student identifier and identifier from the response
                unset($userData['student_identifier'], $userData['identifier']);
            } elseif ($user->hasRole('siswa')) {
                // If the user is a student, remove the teacher identifier and identifier from the response
                unset($userData['teacher_identifier'], $userData['identifier']);
            }

            // Return success with Api Resource
            return new UserResource(true, 'Detail Data User', $userData);
        }

        // Return failed with Api Resource
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
            // Get user details with roles and appropriate identifier relation
            $userDetails = User::with(['roles', 'teacherIdentifier', 'studentIdentifier'])->find($currentUser->id);

            // Initialize an array to hold the user details
            $userData = $userDetails->toArray();

            // Check if the user is a teacher
            if ($userDetails->hasRole('guru')) {
                // If the user is a teacher, remove the student identifier and identifier from the response
                unset($userData['student_identifier'], $userData['identifier']);
            } elseif ($userDetails->hasRole('siswa')) {
                // If the user is a student, remove the teacher identifier and identifier from the response
                unset($userData['teacher_identifier'], $userData['identifier']);
            }

            // Return success with Api Resource
            return new UserResource(true, 'Detail Data User', $userData);
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
        $userAuth = auth()->guard('api')->user();

        $isUserAdmin = false; // Default value false, jika pengguna tidak memiliki peran admin

        if ($userAuth) { // Memeriksa apakah pengguna masuk
            $userRoles = $userAuth->roles->pluck('name'); // Mendapatkan daftar peran pengguna
            $isUserAdmin = $userRoles->contains('admin'); // Memeriksa apakah pengguna memiliki peran 'admin'
        }

        $roles = $request->input('roles', []);

        // Check if pengurus_kelas role is selected but siswa role is not selected, then automatically add siswa role
        if (in_array('pengurus_kelas', $roles) && !in_array('siswa', $roles)) {
            $roles[] = 'siswa';
        }

        $validator = Validator::make($request->all(), [
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|integer|unique:users,nomor_absen,NULL,id,student_class_id,' . $request->class_id : 'nullable',
            'name' => 'required|min:3',
            'email' => [
                'required',
                'unique:users',
                function ($attribute, $value, $fail) {
                    // Lakukan validasi menggunakan regex untuk memastikan alamat email sesuai format
                    if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $value)) {
                        $fail('Format email tidak valid.');
                    }
                },
            ],
            'password' => 'required|confirmed',
            'roles' => 'required',
            'phone_number' => [
                'required',
                'regex:/^(08|\+62|\+\d{1,15})\d{9,13}$/',
                function ($attribute, $value, $fail) {
                    // Mengeksekusi kueri untuk memeriksa apakah nomor telepon ada dalam format yang sesuai di database
                    if (User::where('phone_number', $value)
                        ->orWhere('phone_number', '+62' . ltrim($value, '0'))
                        ->orWhere('phone_number', '+' . ltrim($value, '0'))
                        ->exists()
                    ) {
                        // Jika nomor telepon sudah ada dalam format yang sesuai di database, kirim pesan kesalahan
                        $fail('Nomor Telepon sudah ada sebelumnya.');
                    }
                },
            ],
            'class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|exists:student_classes,id' : 'nullable',
            'nisn' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|min:10' : 'nullable',
            'nip' => (in_array('guru', $roles)) ? 'required|min:10' : 'nullable',
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? 'required|in:RPL - Produktif,Animasi - Produktif,Broadcasting - Produktif,TO - Produktif,TPFL - Produktif,Matematika,Sejarah,Pendidikan Agama,IPAS,Olahraga,Bahasa Indonesia,Bahasa Sunda,Bahasa Inggris,Bahasa Jepang' : 'nullable',
        ], [
            'nomor_absen.required' => 'Nomor Absen wajib diisi untuk peran Siswa atau Pengurus Kelas.',
            'nomor_absen.integer' => 'Nomor Absen harus berupa angka.',
            'nomor_absen.unique' => 'Nomor Absen sudah digunakan.',

            'nisn.required' => 'NISN wajib diisi.',
            'nisn.min' => 'NISN harus terdiri dari minimal :min karakter.',
            'nisn.unique' => 'NISN sudah digunakan.',

            'nip.required' => 'NIP wajib diisi.',
            'nip.min' => 'NIP harus terdiri dari minimal :min karakter.',
            'nip.unique' => 'NIP sudah digunakan.',

            'name.required' => 'Nama wajib diisi.',

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

        // Mendapatkan nomor telepon dari input
        $phoneNumber = $request->phone_number;

        // Proses untuk mengubah nomor telepon ke format internasional
        if (preg_match('/^08/', $phoneNumber)) {
            // Jika nomor telepon dimulai dengan '08', ubah ke format internasional
            $formattedPhoneNumber = '+62' . substr($phoneNumber, 1);
        } else {
            // Jika nomor telepon tidak dimulai dengan '08', tetap gunakan nomor telepon yang sama
            $formattedPhoneNumber = $phoneNumber;
        }

        // Periksa apakah request roles memuat peran admin
        if (in_array('admin', $roles)) {
            // Periksa apakah pengguna yang sedang login adalah admin
            if (!$userAuth && !$isUserAdmin) {
                // Jika pengguna tidak login sebagai admin, kembalikan error
                return response()->json(['error' => 'Anda tidak boleh mendaftar sebagai admin.'], 422);
            }
        }

        if (!$userAuth && !$isUserAdmin) {
            // Jika pengguna tidak login atau tidak memiliki role admin, lakukan validasi NISN atau nip
            if (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) {
                $studentIdentifier = StudentIdentifier::where('nisn', $request->nisn)->first();
                if (!$studentIdentifier) {
                    return response()->json(['error' => 'NISN tidak terdaftar. Anda tidak diizinkan mendaftar akun.'], 422);
                }
                if ($studentIdentifier->student_id) {
                    return response()->json(['error' => 'Akun dengan NISN yang dimasukkan sudah ada sebelumnya.'], 422);
                }
            } elseif (in_array('guru', $roles)) {
                $teacherIdentifier = TeacherIdentifier::where('nip', $request->nip)->first();
                if (!$teacherIdentifier) {
                    return response()->json(['error' => 'NIP tidak terdaftar. Anda tidak diizinkan mendaftar akun.'], 422);
                }
                if ($teacherIdentifier->teacher_id) {
                    return response()->json(['error' => 'Akun dengan NIP yang dimasukkan sudah ada sebelumnya.'], 422);
                }
            }
        } else {
            if (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) {
                $studentIdentifier = StudentIdentifier::where('nisn', $request->nisn)->first();
                if ($studentIdentifier && $studentIdentifier->student_id) {
                    return response()->json(['error' => 'Akun dengan NISN yang dimasukkan sudah ada sebelumnya.'], 422);
                }
            } elseif (in_array('guru', $roles)) {
                $teacherIdentifier = TeacherIdentifier::where('nip', $request->nip)->first();
                if ($teacherIdentifier && $teacherIdentifier->teacher_id) {
                    return response()->json(['error' => 'Akun dengan NIP yang dimasukkan sudah ada sebelumnya.'], 422);
                }
            }
        }
 
        // Buat user
        $user = User::create([
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->nomor_absen : null,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $formattedPhoneNumber,
            'student_class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? $request->class_id : null,
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? $request->guru_mata_pelajaran : null,
        ]);

        // Assign roles to user
        $user->assignRole($roles);

        if (!$userAuth && !$isUserAdmin) {
            // Update NISN and nip with user_id based on roles
            if (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) {
                $studentIdentifierUpdate = StudentIdentifier::where('nisn', $request->nisn)->first();
                if ($studentIdentifierUpdate) {
                    $studentIdentifierUpdate->update(['student_id' => $user->id]);
                } else {
                    // Handle if student identifier not found
                    return response()->json(['error' => 'Data NISN tidak ditemukan.'], 422);
                }
            } elseif (in_array('guru', $roles)) {
                $teacherIdentifierUpdate = TeacherIdentifier::where('nip', $request->nip)->first();
                if ($teacherIdentifierUpdate) {
                    $teacherIdentifierUpdate->update(['teacher_id' => $user->id]);
                } else {
                    // Handle if teacher identifier not found
                    return response()->json(['error' => 'Data NIP tidak ditemukan.'], 422);
                }
            }
        } else {
            // Admin sedang membuat akun, tambahkan NISN atau nip baru jika tidak ditemukan di database
            if (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) {
                $studentIdentifierAdmin = StudentIdentifier::where('nisn', $request->nisn)->first();
                if (!$studentIdentifierAdmin) {
                    // NISN tidak ditemukan, tambahkan NISN baru ke database
                    $studentIdentifierAdmin = StudentIdentifier::create([
                        'nisn' => $request->nisn,
                        'student_id' => $user->id,
                    ]);
                } else {
                    $studentIdentifierAdmin->update(['student_id' => $user->id]);
                }
            } elseif (in_array('guru', $roles)) {
                $teacherIdentifierAdmin = TeacherIdentifier::where('nip', $request->nip)->first();
                if (!$teacherIdentifierAdmin) {
                    // nip tidak ditemukan, tambahkan nip baru ke database
                    $teacherIdentifierAdmin = TeacherIdentifier::create([
                        'nip' => $request->nip,
                        'teacher_id' => $user->id,
                    ]);
                } else {
                    $teacherIdentifierAdmin->update(['teacher_id' => $user->id]);
                }
            }
        }

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
                // Get the teacher id from the task
                $teacherId = $task->creator_id;

                // Create student tasks with teacher id
                $studentTask = new StudentTasks([
                    'student_id' => $studentId,
                    'task_id' => $task->id,
                    'teacher_id' => $teacherId,
                    // Add additional information if needed
                ]);
                $studentTask->save();

                // Send notification to the student
                $student = User::find($studentId);
                if ($student) {
                    // Get the teacher name
                    $teacher = User::find($teacherId);
                    $guruName = $teacher->name;
                    $student->notify(new TaskNotification($task, $guruName));
                }
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
    public function update(Request $request, $id)
    {
        $loggedInUser = auth()->user();
        $isUserAdminUpdate = false; // Default value false, jika pengguna tidak memiliki peran admin

        if ($loggedInUser) { // Memeriksa apakah pengguna masuk
            $userRoles = $loggedInUser->roles->pluck('name'); // Mendapatkan daftar peran pengguna
            $isUserAdminUpdate = $userRoles->contains('admin'); // Memeriksa apakah pengguna memiliki peran 'admin'
        }

        $user = User::findOrFail($id);
        $roles = $request->input('roles', []);

        // Check if pengurus_kelas role is selected but siswa role is not selected, then automatically add siswa role
        if (in_array('pengurus_kelas', $roles) && !in_array('siswa', $roles)) {
            $roles[] = 'siswa';
        }

        $validator = Validator::make($request->all(), [
            'nomor_absen' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|integer' : 'nullable',
            'name' => 'required',
            'email' => [
                'required',
                'unique:users',
                function ($attribute, $value, $fail) {
                    // Lakukan validasi menggunakan regex untuk memastikan alamat email sesuai format
                    if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $value)) {
                        $fail('Format email tidak valid.');
                    }
                },
            ],
            'nisn' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|min:10|unique:student_identifiers,nisn' : 'nullable',
            'nip' => (in_array('guru', $roles)) ? 'required|min:10|unique:teacher_identifiers,nip' : 'nullable',
            'password' => 'nullable|confirmed',
            'roles' => 'required',
            'phone_number' => 'required|numeric',
            'class_id' => (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required|exists:student_classes,id' : 'nullable',
            'guru_mata_pelajaran' => (in_array('guru', $roles)) ? 'required|in:RPL - Produktif,Animasi - Produktif,Broadcasting - Produktif,TO - Produktif,TPFL - Produktif,Matematika,Sejarah,Pendidikan Agama,IPAS,Olahraga,Bahasa Indonesia,Bahasa Sunda,Bahasa Inggris,Bahasa Jepang' : 'nullable',
        ], [
            'nomor_absen.required' => 'Nomor Absen wajib diisi untuk peran Siswa atau Pengurus Kelas.',
            'nomor_absen.integer' => 'Nomor Absen harus berupa angka.',
            'nomor_absen.unique' => 'Nomor Absen sudah digunakan.',

            'nisn.required' => 'NISN wajib diisi.',
            'nisn.min' => 'NISN harus terdiri dari minimal :min karakter.',
            'nisn.unique' => 'NISN sudah digunakan.',

            'nip.required' => 'NIP wajib diisi.',
            'nip.min' => 'NIP harus terdiri dari minimal :min karakter.',
            'nip.unique' => 'NIP sudah digunakan.',

            'name.required' => 'Nama wajib diisi.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

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

        if ($isUserAdminUpdate) {
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

            // Update NISN or nip if provided
            if ($request->has('nisn')) {
                $user->studentIdentifier()->updateOrCreate(
                    ['nisn' => $request->nisn],
                    ['student_id' => $user->id]
                );
            } elseif ($request->has('nip')) {
                $user->teacherIdentifier()->updateOrCreate(
                    ['nip' => $request->nip],
                    ['teacher_id' => $user->id]
                );
            }

            // Sync roles for the user
            $user->syncRoles($request->roles);

            // Check if permissions are provided and sync them
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            // Return success with Api Resource
            return new UserResource(true, 'Data User Berhasil Diupdate!', $user);
        } elseif (!$isUserAdminUpdate && ($loggedInUser->id === $user->id)) {
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

            // Update NISN or nip if provided
            if ($request->has('nisn')) {
                $user->studentIdentifier()->updateOrCreate(
                    ['nisn' => $request->nisn],
                    ['student_id' => $user->id]
                );
            } elseif ($request->has('nip')) {
                $user->teacherIdentifier()->updateOrCreate(
                    ['nip' => $request->nip],
                    ['teacher_id' => $user->id]
                );
            }

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
