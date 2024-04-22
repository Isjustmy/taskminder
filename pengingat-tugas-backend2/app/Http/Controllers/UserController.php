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
use App\Models\UserTokenFcm;
use App\Notifications\TaskNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function storeUserTokenFcm(Request $request)
    {
        $user = Auth::user();

        // Validasi request
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Untuk mencegah terjadinya celah user/orang lain dapat memasukan token sembarangan kepada user lain.
        // Bandingkan email pengguna yang sedang login dengan email dalam permintaan
        if ($user->email !== $request->email) {
            return response()->json(['error' => 'Email tidak sesuai dengan pengguna yang sedang login'], 403);
        }

        // Temukan user berdasarkan email
        $getUserId = User::where('email', $request->email)->first();

        if (!$getUserId) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        // Perbarui token FCM user
        $userTokenFcm = UserTokenFcm::updateOrCreate(
            ['user_id' => $getUserId->id],
            ['token' => $request->token]
        );

        if ($userTokenFcm) {
            return response()->json(['message' => 'Token FCM berhasil dimasukkan/diperbarui'], 200);
        }

        return response()->json(['error' => 'Terjadi kesalahan saat memperbarui token FCM'], 500);
    }


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


    public function getTeacherData(Request $request)
    {
        // Memeriksa apakah parameter mata pelajaran disertakan dalam permintaan
        if (!$request->has('subject')) {
            return response()->json([
                'success' => false,
                'message' => 'Mata pelajaran (subject) diperlukan pada request untuk mendapatkan data guru.',
            ], 400);
        }

        try {
            // Mendapatkan semua user yang memiliki peran "guru" dengan mata pelajaran yang sesuai
            $subject = $request->input('subject');
            $teachers = User::whereHas('roles', function ($query) {
                $query->where('name', 'guru');
            })->where('guru_mata_pelajaran', $subject)->select('id', 'name', 'email', 'phone_number',  'guru_mata_pelajaran')->get();

            // Memeriksa apakah ada data guru yang ditemukan
            if ($teachers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data guru dengan mata pelajaran ' . $subject . ' tidak ditemukan.',
                ], 404);
            }

            // Mengirimkan data guru jika berhasil ditemukan
            return response()->json([
                'success' => true,
                'message' => 'Data guru dengan mata pelajaran ' . $subject . ' berhasil ditemukan.',
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


    public function getSiswaUsers($classId)
    {
        try {
            // Retrieve student users based on the class ID
            $users = User::role('siswa')->where('student_class_id', $classId)->with('studentClass')->paginate(5);

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data siswa tidak ditemukan untuk kelas yang dipilih.',
                    'data' => []
                ], 404);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users->items() as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil diambil untuk kelas yang dipilih.',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data siswa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getPengurusKelasUsers($idKelas)
    {
        try {
            $users = User::role('pengurus_kelas')->where('student_class_id', $idKelas)->with('studentClass')->get();
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengurus kelas pada kelas ini tidak ditemukan.',
                    'data' => []
                ], 404);
            }

            // Menghilangkan student_class_id dari setiap pengguna
            foreach ($users as $user) {
                unset($user->student_class_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pengurus kelas berhasil diambil.',
                'data' => $users
            ], 200);
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
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan data kelas dan mata pelajaran.',
                'error' => $e->getMessage(),
                'data' => null,
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

        // Modify the data to place student_class at the same level as student_class_id
        $users->getCollection()->transform(function ($user) {
            // If student_class_id is not null, set student_class as an object with id and class
            if ($user->student_class_id !== null) {
                $user->student_class = [
                    'id' => $user->studentClass->id,
                    'class' => $user->studentClass->class,
                ];
                unset($user->student_class_id);
                unset($user->studentClass); // Remove the original studentClass object
            } else {
                unset($user->student_class_id);
            }
            return $user;
        });

        // Append query string to pagination links
        $users->appends(['search' => request()->search]);

        // Return with Api Resource
        return response()->json([
            'success' => true,
            'message' => 'List Data Users',
            'data' => $users,
        ]);
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
            $userDetails = User::with(['roles', 'teacherIdentifier', 'studentIdentifier', 'studentClass'])->find($currentUser->id);

            // Initialize an array to hold the user details
            $userData = $userDetails->toArray();

            // Check if the user is a teacher
            if ($userDetails->hasRole('guru')) {
                // If the user is a teacher, remove the student identifier and identifier from the response
                unset($userData['student_identifier']);
            } elseif ($userDetails->hasRole('siswa')) {
                // If the user is a student, remove the teacher identifier and identifier from the response
                unset($userData['teacher_identifier']);
                // Add student class data to the response
                $userData['student_class'] = $userDetails->studentClass;
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

        if (in_array('pengurus_kelas', $roles)) {
            $existingPengurusCount = User::role('pengurus_kelas')->where('student_class_id', $request->class_id)->count();
            if ($existingPengurusCount >= 2) {
                return response()->json(['error' => 'Pengurus Kelas dalam satu kelas hanya diperbolehkan sebanyak 2 akun.'], 422);
            }
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
                    } elseif (!in_array(strtolower(substr(strrchr($value, '@'), 1)), ['outlook.com', 'yahoo.com', 'aol.com', 'lycos.com', 'mail.com', 'icloud.com', 'yandex.com', 'protonmail.com', 'tutanota.com', 'zoho.com', 'gmail.com'])) {
                        // Periksa apakah domain email tidak diizinkan
                        $fail('Domain email tidak valid.');
                    }
                },
            ],
            'password' => 'required|min:8|confirmed',
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
            'password.min' => 'Password minimal terdiri dari 8 kombinasi huruf, angka, atau simbol.',
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
                Rule::unique('users')->ignore($id),
                function ($attribute, $value, $fail) {
                    // Lakukan validasi menggunakan regex untuk memastikan alamat email sesuai format
                    if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $value)) {
                        $fail('Format email tidak valid.');
                    } elseif (!in_array(strtolower(substr(strrchr($value, '@'), 1)), ['outlook.com', 'yahoo.com', 'aol.com', 'lycos.com', 'mail.com', 'icloud.com', 'yandex.com', 'protonmail.com', 'tutanota.com', 'zoho.com', 'gmail.com'])) {
                        // Periksa apakah domain email tidak diizinkan
                        $fail('Domain email tidak valid.');
                    }
                },
            ],
            'nisn' => [
                (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'required' : 'nullable',
                (in_array('siswa', $roles) || in_array('pengurus_kelas', $roles)) ? 'min:10' : '',
                Rule::unique('student_identifiers')->ignore($user->studentIdentifier ? $user->studentIdentifier->id : null),
            ],
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
            'password.min' => 'Password minimal terdiri dari 8 kombinasi huruf, angka, atau simbol.',
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
        $loggedInUser = auth()->user();

        // Memeriksa apakah pengguna yang sedang login memiliki peran admin
        if ($loggedInUser->hasRole('admin') && $loggedInUser->id == $id) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus diri sendiri!'], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Data User Tidak Ditemukan!'], 404);
        }

        if ($user->hasRole('guru')) {
            // Menghapus data submit tugas siswa yang terkait dengan task_id dan teacher_id
            $task = Task::where('creator_id', $user->id)->get();

            foreach ($task as $taskData) {
                $studentTaskData = StudentTasks::where('task_id', $taskData->id)->get();

                foreach ($studentTaskData as $studentTask) {
                    if ($studentTask->file_path) {
                        $fileName = basename($studentTask->file_path);
                        Storage::disk('public')->delete('task_files/' . $fileName);
                    }

                    $studentTask->delete();
                }

                // Hapus file terkait jika ada
                if ($taskData->file_path) {
                    // Dapatkan nama file dari URL
                    $fileName = basename($taskData->file_path);

                    // Hapus file dari sistem penyimpanan berdasarkan nama file
                    Storage::disk('public')->delete('files_from_teacher/' . $fileName);
                }

                $taskData->delete();
            }
        } elseif ($user->hasRole('siswa')) {
            StudentTasks::where('student_id', $user->id)->delete();
        }

        if ($user->delete()) {
            return response()->json(['success' => true, 'message' => 'Data User Berhasil Dihapus!']);
        }

        return response()->json(['success' => false, 'message' => 'Data User Gagal Dihapus!'], 500);
    }
}
