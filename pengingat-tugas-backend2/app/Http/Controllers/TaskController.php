<?php

// TaskController.php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Models\StudentClass;
use App\Models\StudentTasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\TaskNotification;
use App\Notifications\TaskCancelledNotification;
use App\Notifications\TaskUpdatedNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function taskSummary()
    {
        $teacherId = Auth::user()->id;

        $tasks = Task::with(['studentTasks.students.studentClass'])
            ->where('creator_id', $teacherId)
            ->get();

        // Periksa apakah tidak ada data tugas
        if ($tasks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data tugas',
                'data' => null,
            ], 404);
        }

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file_path' => $task->file_path ?? '-',
                'link' => $task->link ?? '-',
                'class_id' => $task->class_id,
                'creator_id' => $task->creator_id,
                'mata_pelajaran' => $task->mata_pelajaran,
                'deadline' => $task->deadline,
                'created_at' => $task->created_at,
                'students' => $task->studentTasks->map(function ($studentTask) {
                    $student = $studentTask->students;
                    if ($student) {
                        return [
                            'id' => $student->id,
                            'nomor_absen' => $student->nomor_absen,
                            'name' => $student->name,
                            'student_class' => optional($student->studentClass)->class ?? '-',
                            'submission_info' => [
                                'id_submit' => $studentTask->id,
                                'file_path' => $studentTask->file_path ?? '-',
                                'link' => $studentTask->link ?? '-',
                                'is_submitted' => $studentTask->is_submitted ?? '-',
                                'is_late' => $studentTask->is_late ?? '-',
                                'score' => $studentTask->score ?? '-',
                                'scored_at' => $studentTask->scored_at ?? '-',
                                'submitted_at' => $studentTask->submitted_at ?? '-',
                                'feedback_content' => $studentTask->feedback_content ?? '-',
                            ],
                        ];
                    }
                    return null; // Skip jika tidak ada data siswa
                })->filter()->values()->toArray(), // Filter null dan reset index
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data Tugas yang dibuat',
            'data' => $formattedTasks->values()->toArray(), // Ubah ke array dan reset index
        ]);
    }


    public function taskSummaryWithId($taskId)
    {
        $teacherId = Auth::user()->id;

        $task = Task::with(['studentTasks.students.studentClass'])
            ->where('creator_id', $teacherId)
            ->find($taskId);

        // Periksa apakah tugas ditemukan
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $formattedTask = [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'file_path' => $task->file_path ?? '-',
            'link' => $task->link ?? '-',
            'class_id' => $task->class_id,
            'creator_id' => $task->creator_id,
            'mata_pelajaran' => $task->mata_pelajaran,
            'deadline' => $task->deadline,
            'created_at' => $task->created_at,
            'students' => $task->studentTasks->map(function ($studentTask) {
                $student = $studentTask->students;
                if ($student) {
                    return [
                        'id' => $student->id,
                        'nomor_absen' => $student->nomor_absen,
                        'name' => $student->name,
                        'student_class' => optional($student->studentClass)->class ?? '-',
                        'submission_info' => [
                            'id_submit' => $studentTask->id,
                            'file_path' => $studentTask->file_path ?? '-',
                            'link' => $studentTask->link ?? '-',
                            'is_submitted' => $studentTask->is_submitted ?? '-',
                            'is_late' => $studentTask->is_late ?? '-',
                            'score' => $studentTask->score ?? '-',
                            'scored_at' => $studentTask->scored_at ?? '-',
                            'submitted_at' => $studentTask->submitted_at ?? '-',
                            'feedback_content' => $studentTask->feedback_content ?? '-',
                        ],
                    ];
                }
                return null; // Skip jika tidak ada data siswa
            })->filter()->values()->toArray(), // Filter null dan reset index
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail Tugas',
            'data' => $formattedTask,
        ]);
    }


    public function getTeacherTasks()
    {
        $teacherId = auth()->user()->id;

        // Ambil daftar tugas yang dibuat oleh guru dengan ID yang sedang masuk
        $tasks = Task::with('studentClass:id,class') // Mengambil data kelas terkait dengan tugas
            ->where('creator_id', $teacherId)
            ->get();

        // Menyusun kembali data respons
        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file_path' => $task->file_path,
                'link' => $task->link,
                'class' => [
                    'id' => $task->studentClass->id,
                    'class_name' => $task->studentClass->class,
                ],
                'creator_id' => $task->creator_id,
                'mata_pelajaran' => $task->mata_pelajaran,
                'deadline' => $task->deadline,
                'created_at' => $task->created_at,
            ];
        });

        if ($formattedTasks->isNotEmpty()) {
            return new TaskResource(true, 'Data Tugas yang dibuat', $formattedTasks);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas yang Dibuat', null, 404);
        }
    }


    public function getTeacherTasksWithId($taskId)
    {
        $teacherId = Auth::user()->id;

        // Ambil tugas yang dibuat oleh guru dengan ID yang sedang masuk berdasarkan task ID
        $task = Task::with('studentClass:id,class') // Mengambil data kelas terkait dengan tugas
            ->where('creator_id', $teacherId)
            ->where('id', $taskId)
            ->first();

        if ($task) {
            // Menyusun kembali data respons
            $formattedTask = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file_path' => $task->file_path,
                'link' => $task->link,
                'class' => [
                    'id' => $task->studentClass->id,
                    'class_name' => $task->studentClass->class,
                ],
                'creator_id' => $task->creator_id,
                'mata_pelajaran' => $task->mata_pelajaran,
                'deadline' => $task->deadline,
                'created_at' => $task->created_at,
            ];

            return new TaskResource(true, 'Data Tugas yang dibuat', $formattedTask);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas yang Dibuat', null, 404);
        }
    }

    public function tugasSiswa(Request $request)
    {
        // Mendapatkan ID siswa yang sedang login
        $studentId = Auth::id();

        // Mengambil kelas siswa
        $studentClass = $request->user()->studentClass;

        // Memfilter tugas yang hanya berkaitan dengan siswa itu sendiri dan kelas yang terdaftar dari siswa
        $tasks = Task::where('class_id', $studentClass->id)
            ->whereHas('studentTasks', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak Ada Data Tugas.',
                'data' => null
            ], 404);
        } else {
            // Mengambil informasi pengiriman dari siswa itu sendiri
            $submissionInfo = StudentTasks::where('student_id', $studentId)->get();

            // Menyiapkan data yang akan dikembalikan
            $responseData = $tasks->map(function ($task) use ($submissionInfo) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file_path' => $task->file_path ?? '-',
                    'link' => $task->link ?? '-',
                    'class_id' => $task->class_id,
                    'creator_id' => $task->creator_id,
                    'mata_pelajaran' => $task->mata_pelajaran,
                    'deadline' => $task->deadline,
                    'created_at' => $task->created_at,
                    'submission_info' => $submissionInfo->where('task_id', $task->id)->first(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Detail Tugas Siswa',
                'data' => $responseData,
            ], 200);
        }
    }


    public function tugasSiswaDenganId($taskId)
    {
        $studentId = Auth::user()->id;
        $task = Task::findOrFail($taskId);

        // Periksa apakah tugas diberikan kepada siswa yang sedang login
        if (!$task->studentTasks()->where('student_id', $studentId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Tugas tidak ditemukan.'], 404);
        }

        // Dapatkan tugas siswa
        $tasks = Task::where('id', $taskId)->get();

        $additionalData = StudentTasks::where('task_id', $taskId)->where('student_id', $studentId)->first();

        return response()->json(['success' => true, 'tasks' => $tasks, 'additional_data' => $additionalData ?? null]);
    }


    public function tugasKelasKhusus()
    {
        try {
            // Mendapatkan pengguna yang sedang login
            $user = auth()->user();

            // Memeriksa apakah pengguna memiliki role 'pengurus_kelas'
            if ($user->hasRole('pengurus_kelas')) {
                // Mengambil class_id dari data user pengurus_kelas yang sedang login
                $classId = $user->class_id;

                // Ambil data tugas berdasarkan kelas untuk pengurus kelas
                $tasks = Task::with(['studentTasks.students.studentClass'])
                    ->whereHas('studentClass', function ($query) use ($classId) {
                        $query->where('id', $classId);
                    })
                    ->get();

                // Periksa apakah ada tugas
                if ($tasks->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data tugas untuk kelas ini',
                    ]);
                }

                // Format data tugas
                $formattedTasks = $tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'file_path' => $task->file_path ?? '-',
                        'link' => $task->link ?? '-',
                        'class_id' => $task->class_id,
                        'creator_id' => $task->creator_id,
                        'mata_pelajaran' => $task->mata_pelajaran,
                        'deadline' => $task->deadline,
                        'created_at' => $task->created_at,
                        'students' => $task->studentTasks->map(function ($studentTask) {
                            $student = $studentTask->students;
                            if ($student) {
                                return [
                                    'id' => $student->id,
                                    'nomor_absen' => $student->nomor_absen,
                                    'name' => $student->name,
                                    'student_class_id' => optional($student->studentClass)->id ?? '-',
                                    'submission_info' => [
                                        'file_path' => $studentTask->file_path ?? '-',
                                        'link' => $studentTask->link ?? '-',
                                        'is_submitted' => $studentTask->is_submitted ?? '-',
                                        'score' => $studentTask->score ?? '-',
                                        'scored_at' => $studentTask->scored_at ?? '-',
                                        'submitted_at' => $studentTask->submitted_at ?? '-',
                                        'feedback_content' => $studentTask->feedback_content ?? '-',
                                    ],
                                ];
                            }
                            return null; // Skip jika tidak ada data siswa
                        })->filter()->values()->toArray(), // Filter null dan reset index
                    ];
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Data Tugas berdasarkan kelas untuk pengurus kelas',
                    'data' => $formattedTasks->values()->toArray(),
                ]);
            }

            // Jika pengguna tidak memiliki role 'pengurus_kelas'
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak memiliki akses sebagai pengurus kelas',
            ], 403);
        } catch (\Exception $e) {
            // Tangani kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data tugas',
            ], 500);
        }
    }


    public function tugasPerKelas(Request $request)
    {
        try {
            // Validasi
            $validator = Validator::make($request->all(), [
                'class_id' => 'required|exists:student_classes,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Ambil ID kelas dari request
            $classId = $request->input('class_id');

            // Ambil data tugas dari kelas tertentu
            $tasks = Task::with(['studentTasks.students.studentClass'])
                ->where('class_id', $classId)
                ->get();

            // Periksa apakah ada tugas
            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data tugas untuk kelas ini',
                ]);
            }

            // Format data tugas
            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file_path' => $task->file_path ?? '-',
                    'link' => $task->link ?? '-',
                    'class_id' => $task->class_id,
                    'creator_id' => $task->creator_id,
                    'mata_pelajaran' => $task->mata_pelajaran,
                    'deadline' => $task->deadline,
                    'created_at' => $task->created_at,
                    'students' => $task->studentTasks->map(function ($studentTask) {
                        $student = $studentTask->students;
                        if ($student) {
                            return [
                                'id' => $student->id,
                                'nomor_absen' => $student->nomor_absen,
                                'name' => $student->name,
                                'student_class_id' => optional($student->studentClass)->id ?? '-',
                                'submission_info' => [
                                    'file_path' => $studentTask->file_path ?? '-',
                                    'link' => $studentTask->link ?? '-',
                                    'is_submitted' => $studentTask->is_submitted ?? '-',
                                    'score' => $studentTask->score ?? '-',
                                    'scored_at' => $studentTask->scored_at ?? '-',
                                    'submitted_at' => $studentTask->submitted_at ?? '-',
                                    'feedback_content' => $studentTask->feedback_content ?? '-',
                                ],
                            ];
                        }
                        return null; // Skip jika tidak ada data siswa
                    })->filter()->values()->toArray(), // Filter null dan reset index
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Tugas berdasarkan kelas',
                'data' => $formattedTasks->values()->toArray(),
            ]);
        } catch (\Exception $e) {
            // Tangani kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data tugas',
            ], 500);
        }
    }


    public function getTaskAndSubmissionData(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'student_task_id' => 'required|exists:student_tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $taskId = $request->task_id;
        $studentTaskId = $request->student_task_id;

        // Dapatkan data tugas berdasarkan ID
        $task = Task::with(['studentTasks.students.studentClass'])
            ->where('id', $taskId)
            ->first();

        // Periksa apakah tugas ditemukan
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Dapatkan data submit siswa berdasarkan ID student task
        $submission = StudentTasks::find($studentTaskId);

        // Periksa apakah submit siswa ditemukan
        if (!$submission) {
            return response()->json([
                'success' => false,
                'message' => 'Submit siswa tidak ditemukan',
                'data' => null,
            ], 404);
        }

        // Format data tugas
        $formattedTask = [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'file_path' => $task->file_path ?? '-',
                'link' => $task->link ?? '-',
                'class_id' => $task->class_id,
                'creator_id' => $task->creator_id,
                'mata_pelajaran' => $task->mata_pelajaran,
                'deadline' => $task->deadline,
                'created_at' => $task->created_at,
            ],
            'submission' => [
                'id' => $submission->id,
                'file_path' => $submission->file_path ?? '-',
                'link' => $submission->link ?? '-',
                'is_submitted' => $submission->is_submitted ?? '-',
                'is_late' => $submission->is_late ?? '-',
                'score' => $submission->score ?? '-',
                'scored_at' => $submission->scored_at ?? '-',
                'submitted_at' => $submission->submitted_at ?? '-',
                'feedback_content' => $submission->feedback_content ?? '-',
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data tugas dan submit siswa berhasil diambil',
            'data' => $formattedTask,
        ]);
    }


    public function all()
    {
        $tasks = Task::with('studentClass')->paginate(5);

        if ($tasks->count() > 0) {
            // Mengubah struktur data kelas untuk setiap tugas
            $tasks->getCollection()->transform(function ($task) {
                unset($task->class_id); // Menghapus properti class_id
                return $task;
            });

            return new TaskResource(true, 'Semua Data Tugas', $tasks);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas.', null);
        }
    }


    // for admin
    public function show($id)
    {
        $task = Task::findOrFail($id);

        if ($task) {
            return new TaskResource(true, 'Detail Data Tugas Berdasarkan ID', $task);
        } else {
            return new TaskResource(false, 'Detail Data Tugas Tidak Ditemukan.', null, 404);
        }
    }


    // kode untuk membuat tugas untuk satu siswa saja
    // public function createTaskForStudent(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|string|max:75',
    //         'description' => 'required|string',
    //         'student_id' => 'required|exists:users,id',
    //         'deadline' => 'required|date',
    //         'file' => 'nullable|file|max:2048',
    //         'link' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return new TaskResource(false, 'Validasi gagal', $validator->errors());
    //     }

    //     $creatorId = auth()->user()->id;

    //     // Get the user with the specified 'student_id'
    //     $student = User::find($request->input('student_id'));

    //     // Check if the user has the 'siswa' role
    //     if ($student && $student->hasRole('siswa')) {
    //         $taskData = $request->only(['title', 'description', 'deadline']);
    //         $taskData['creator_id'] = $creatorId;
    //         $taskData['created_at'] = Carbon::now();
    //         $taskData['file_path'] = $request->hasFile('file') ? $request->file('file')->store('files_from_teacher', 'public') : null;
    //         $taskData['link'] = $request->input('link');

    //         $task = Task::create($taskData);

    //         if ($task) {
    //             $student->notify(new TaskNotification($task, auth()->user()->name));

    //             return new TaskResource(true, 'Tugas berhasil dibuat untuk siswa', $task);
    //         } else {
    //             return new TaskResource(false, 'Gagal membuat tugas.', null);
    //         }
    //     } else {
    //         return new TaskResource(false, 'User dengan student_id tidak ditemukan atau bukan siswa.', null);
    //     }
    // }


    // kode untuk membuat tugas untuk semua siswa didalam satu kelas tertentu
    public function createTaskForClass(Request $request)
    {
        // Periksa role pengguna yang sedang login
        $user = Auth::user();

        // Validasi
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:75',
            'description' => 'required|string',
            'class_id' => (($user->hasRole('guru') || $user->hasRole('admin')) ? 'required|integer|exists:student_classes,id' : 'nullable'),
            'deadline' => 'required|date',
            'teacher_id' => (($user->hasRole('pengurus_kelas') || $user->hasRole('admin')) ? 'required|integer|exists:users,id' : 'nullable'),
            'file' => 'nullable|file|max:2048',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors(), 422);
        }

        // Ambil ID kelas dari pengurus_kelas yang sedang login
        $class_id = null;
        if ($user->hasRole('pengurus_kelas')) {
            // Dapatkan data pengurus_kelas yang sedang login
            $pengurusKelas = User::with('studentClass')->find($user->id);

            // Periksa apakah data pengurus_kelas ditemukan
            if ($pengurusKelas) {
                // Ambil ID kelas dari pengurus_kelas
                $class_id = $pengurusKelas->studentClass->id;
            }
        } elseif ($user->hasRole('guru') || $user->hasRole('admin')) {
            // Jika role adalah guru atau admin, gunakan class_id yang disediakan dalam permintaan
            $class_id = $request->input('class_id');
        }

        if (!$class_id) {
            // Jika tidak ada class_id yang tersedia, kembalikan pesan kesalahan
            return new TaskResource(false, 'ID kelas tidak ditemukan', null);
        }

        $creatorId = null;
        $guruName = null;
        $mataPelajaran = null; // Tambahkan variabel untuk menyimpan mata pelajaran

        if ($user->hasRole('guru')) {
            // Jika guru yang login, gunakan ID guru
            $creatorId = $user->id;
            $guruName = $user->name;
            $mataPelajaran = $user->guru_mata_pelajaran; // Ambil mata pelajaran dari user guru
        } elseif ($user->hasAnyRole(['pengurus_kelas', 'admin'])) {
            // Jika pengurus_kelas atau admin yang membuat tugas
            if (!$request->has('teacher_id')) {
                // Tampilkan pesan jika teacher_id tidak disertakan
                return new TaskResource(false, 'Silakan masukkan ID guru yang akan menetapkan tugas.', null);
            }

            $guruIdFromInput = $request->input('teacher_id');

            // Periksa apakah ID guru valid
            $isGuru = User::where('id', $guruIdFromInput)
                ->role('guru')
                ->first();

            if (!$isGuru) {
                // Guru tidak ditemukan. Periksa kembali ID yang Anda masukkan.
                return new TaskResource(false, 'Guru tidak ditemukan. Periksa kembali ID yang Anda masukkan.', null);
            }

            // Gunakan ID guru yang dimasukkan oleh pengurus_kelas
            $creatorId = $guruIdFromInput;
            $guruName = $isGuru->name;
            $mataPelajaran = $isGuru->guru_mata_pelajaran; // Ambil mata pelajaran dari user guru
        }

        // Menyimpan file penugasan dari guru jika ada
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('files_from_teacher', 'public');
        }

        // Buat data tugas
        $taskData = $request->only(['title', 'description', 'deadline']);
        $taskData['class_id'] = $class_id;
        $taskData['creator_id'] = $creatorId;
        $taskData['created_at'] = Carbon::now();
        $taskData['mata_pelajaran'] = $mataPelajaran; // Simpan mata pelajaran ke dalam data tugas

        // Buat tugas
        $task = Task::create($taskData);

        // Simpan path file ke dalam kolom 'file_path' pada tabel 'tasks'
        $task->file_path = $filePath;
        $task->link = $request->link;
        $task->save();

        // Temukan kelas berdasarkan ID yang telah diambil sebelumnya
        $class = StudentClass::findOrFail($class_id);

        // Ambil daftar siswa dalam kelas
        $students = $class->students;

        // Buat entri tugas untuk setiap siswa
        foreach ($students as $student) {
            $studentTask = new StudentTasks([
                'student_id' => $student->id,
                'task_id' => $task->id,
                'teacher_id' => $creatorId,
                'is_submitted' => false,
                // Jika diperlukan, tambahkan informasi tambahan di sini
            ]);
            $studentTask->save();

            $student->notify(new TaskNotification($task, $guruName));
        }

        // Data untuk respons
        $responseData = [
            'title' => $task->title,
            'description' => $task->description,
            'class' => [
                'id' => $class->id,
                'class_name' => $class->class,
                'students' => [],
            ],
            'deadline' => $task->deadline,
            'creator_id' => $task->creator_id,
            'created_at' => $task->created_at,
            'file_path' => $task->file_path,
            'link' => $task->link,
            'mata_pelajaran' => $task->mata_pelajaran, // Sertakan mata pelajaran dalam respons
            'id' => $task->id,
        ];

        // Tambahkan data siswa ke dalam respons
        foreach ($students as $student) {
            $responseData['class']['students'][] = [
                'id' => $student->id,
                'name' => $student->name,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat untuk kelas',
            'data' => $responseData,
        ], 201);
    }


    public function submitTaskByStudent(Request $request, $taskId)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:2000',
            'link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Dapatkan informasi siswa yang sedang masuk
        $studentId = auth()->user()->id;

        // Cari tugas berdasarkan ID
        $task = Task::findOrFail($taskId);

        // Siapkan data yang akan di-submit
        $submittedTaskData = [
            'student_id' => $studentId,
            'task_id' => $taskId,
            'link' => $request->input('link'),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // Tentukan apakah tugas di-submit terlambat
        $isLateSubmission = $task->deadline <= Carbon::now();

        // Hapus file lama jika ada
        if ($request->file('file')) {
            // Periksa apakah ada entri submission yang sudah ada
            $existingSubmission = StudentTasks::where('task_id', $taskId)
                ->where('student_id', $studentId)
                ->first();

            // Jika ada entri submission yang sudah ada dan memiliki file_path, hapus file tersebut
            if ($existingSubmission && $existingSubmission->file_path) {
                $fileNameReal = basename($existingSubmission->file_path);
                Storage::disk('public')->delete('task_files/' . $fileNameReal);
            }

            $filePath = null;

            $filePath = $request->file('file')->store('task_files', 'public');
            $submittedTaskData['file_path'] = $filePath;
        }

        // Simpan data tugas siswa
        $submittedTask = StudentTasks::updateOrCreate(
            ['task_id' => $taskId, 'student_id' => $studentId],
            $submittedTaskData + ['is_late' => $isLateSubmission]
        );

        if ($submittedTask) {
            return new TaskResource(true, 'Tugas berhasil disubmit oleh siswa', $submittedTask);
        } else {
            return new TaskResource(false, 'Gagal submit tugas.', null);
        }
    }


    public function gradeTaskByTeacher(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer',
            'feedback_content' => 'required'
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        $teacherId = auth()->user()->id;

        // Pastikan tugas dimiliki oleh guru yang sedang memberikan nilai
        $task = Task::where('id', $id)
            ->where('creator_id', $teacherId)
            ->firstOrFail();

        // Pastikan tugas sudah di-submit oleh siswa
        $submission = StudentTasks::where('task_id', $id)
            ->where('is_submitted', true)
            ->first();

        // Update nilai dan waktu penilaian pada entitas student_tasks
        $submission->update([
            'score' => $request->score,
            'feedback_content' => $request->feedback_content,
            'scored_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return new TaskResource(true, 'Tugas berhasil dinilai oleh guru', $task);
    }

    public function update(Request $request, $taskId)
    {
        $userAuth = auth()->guard('api')->user();
        $isUserGuru = false; // Default value false, jika pengguna tidak memiliki peran Guru

        if ($userAuth) { // Memeriksa apakah pengguna masuk
            $userRoles = $userAuth->roles->pluck('name'); // Mendapatkan daftar peran pengguna
            $isUserGuru = $userRoles->contains('guru'); // Memeriksa apakah pengguna memiliki peran 'admin'
        }

        // Validasi masukan pengguna
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:75',
            'description' => 'required|string',
            'class_id' => 'required|integer|exists:student_classes,id',
            'deadline' => 'required|date',
            'teacher_id' => (!$isUserGuru ? 'required|integer|exists:users,id' : 'nullable'),
            'file' => 'nullable',
            'link' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Temukan tugas yang akan diperbarui
        $task = Task::findOrFail($taskId);

        // Simpan data tugas sebelum pembaruan
        $oldTask = clone $task;

        // Jika pengguna adalah seorang guru, tetapkan teacher_id dari pengguna yang masuk
        $teacherId = null;
        if ($isUserGuru) {
            $teacherId = $userAuth->id;
        } else {
            // Jika bukan seorang guru, pastikan mereka memberikan teacher_id dalam permintaan
            $teacherId = $request->input('teacher_id');
            if (!$teacherId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan masukkan ID guru yang akan menetapkan tugas.',
                ], 400);
            }
        }

        // Periksa apakah ID guru yang diberikan valid
        if ($teacherId) {
            $isGuru = User::where('id', $teacherId)
                ->role('guru')
                ->exists();
            if (!$isGuru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak ditemukan. Periksa kembali ID yang Anda masukkan.',
                ], 404);
            }
        }

        // Perbarui path file pada tugas jika ada file yang diunggah
        $taskData = $request->only(['title', 'description', 'deadline', 'teacher_id', 'link']);

        $old_taskPath = $oldTask->file_path;

        // Periksa apakah ada file yang diunggah dalam permintaan
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($old_taskPath) {
                // Dapatkan nama file dari URL
                $oldFile = basename($old_taskPath);

                // Hapus file lama dari sistem penyimpanan
                Storage::disk('public')->delete('files_from_teacher/' . $oldFile);
            }

            // Simpan file yang baru di storage
            $filePath = $request->file('file')->store('files_from_teacher', 'public');
            $taskData['file_path'] = $filePath;
        }

        // Jika tugas tidak mengubah id kelas
        if ($task->class_id == $request->input('class_id')) {
            // Update tugas
            $task->update($taskData);

            // Notifikasi kepada siswa dalam kelas
            $class = StudentClass::findOrFail($task->class_id);
            $students = $class->students;
            foreach ($students as $student) {
                $student->notify(new TaskUpdatedNotification($task));
            }

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui.',
                'data' => $task,
            ]);
        }

        // Jika tugas mengubah id kelas
        // Batalkan tugas untuk siswa di kelas sebelumnya
        $previousClass = StudentClass::findOrFail($task->class_id);
        $previousStudents = $previousClass->students;
        foreach ($previousStudents as $student) {
            $studentTask = StudentTasks::where('student_id', $student->id)
                ->where('task_id', $task->id)
                ->first();
            if ($studentTask) {
                $studentTask->delete();
                $student->notify(new TaskCancelledNotification($oldTask));
            }
        }

        // Buat tugas baru untuk siswa di kelas baru
        $newClassId = $request->input('class_id');
        $newClass = StudentClass::findOrFail($newClassId);
        $newStudents = $newClass->students;
        foreach ($newStudents as $student) {
            $studentTask = new StudentTasks([
                'student_id' => $student->id,
                'task_id' => $task->id,
                'teacher_id' => $teacherId,
                'is_submitted' => false,
            ]);
            $studentTask->save();
            $teacherName = User::findOrFail($teacherId)->name; // Mendapatkan nama guru dari ID guru
            $student->notify(new TaskNotification($task, $teacherName));
        }

        // Update informasi tugas
        $task->update(array_merge($taskData, ['class_id' => $request->input('class_id'), 'file_path' => $filePath]));

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui dengan pindah kelas.',
            'data' => $task,
        ]);
    }


    /**
     * Menghapus tugas dari guru secara permanen.
     * PERINGATAN: TUGAS SISWA YANG TELAH DISUBMIT JUGA AKAN TERHAPUS!
     */
    public function deleteTaskFromTeacher($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Mendapatkan kelas terkait dengan tugas
            $class = $task->studentClass;

            // Jika kelas tidak ditemukan, kembalikan respon error
            if (!$class) {
                return new TaskResource(false, 'Kelas tidak ditemukan untuk tugas ini.', null);
            }

            // Mendapatkan siswa yang terdaftar dalam kelas
            $students = $class->students;

            // Array untuk menyimpan ID tugas yang gagal dikirim notifikasi
            $failedNotifications = [];

            // Mengirim notifikasi kepada setiap siswa
            foreach ($students as $student) {
                try {
                    $student->notify(new TaskCancelledNotification($task));
                } catch (\Exception $e) {
                    // Jika gagal mengirim notifikasi, tambahkan ID tugas ke dalam array failedNotifications
                    $failedNotifications[] = $student->id;
                }
            }

            // Menghapus data submit tugas siswa yang terdaftar
            $studentTaskData = StudentTasks::where('task_id', $id)->where('teacher_id', $task->creator_id)->get();

            foreach ($studentTaskData as $studentTask) {
                if ($studentTask->file_path) {
                    $fileName = basename($studentTask->file_path);
                    Storage::disk('public')->delete('task_files/' . $fileName);
                }

                $studentTask->delete();
            }

            // Hapus file terkait jika ada
            if ($task->file_path) {
                $fileName = basename($task->file_path);
                Storage::disk('public')->delete('files_from_teacher/' . $fileName);
            }

            // Hapus notifikasi terkait dengan tugas
            DB::table('notifications')->where('data->task_id', $task->id)->delete();

            // Hapus tugas berdasarkan ID yang diberikan
            $task->delete();

            // Jika ada notifikasi yang gagal dikirim, kembalikan respon dengan pesan yang sesuai
            if (!empty($failedNotifications)) {
                return new TaskResource(false, 'Tugas berhasil dihapus, tetapi notifikasi tidak dapat dikirim kepada beberapa siswa.', $failedNotifications);
            }

            return new TaskResource(true, 'Tugas berhasil dihapus beserta data submit tugas siswa yang terkait', []);
        } catch (ModelNotFoundException $e) {
            return new TaskResource(false, 'Tugas tidak ditemukan!', null);
        } catch (\Exception $e) {
            return new TaskResource(false, 'Gagal menghapus tugas.', null);
        }
    }


    /**
     * Dihapus disini maksudnya TIDAK dihapus permanen, melainkan mereset data submit tugas
     * ke nilai default, yaitu semua NULL kecuali beberapa kolom yang dibutuhkan untuk skema
     * sistem penugasan.
     */
    public function deleteTaskFromStudent($taskId)
    {
        try {
            // Dapatkan ID guru yang terkait dengan tugas
            $task = Task::findOrFail($taskId);
            $teacherId = $task->creator_id;

            // Dapatkan ID siswa yang sedang login
            $studentId = Auth::id();

            // Cari data submit tugas siswa berdasarkan ID tugas, ID siswa, dan ID guru
            $studentTask = StudentTasks::where('task_id', $taskId)
                ->where('student_id', $studentId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();

            // Dapatkan nama file dari URL
            $fileNameStudent = basename($studentTask->file_path);

            // Hapus file dari sistem penyimpanan berdasarkan nama file
            Storage::disk('public')->delete('task_files/' . $fileNameStudent);

            // Reset nilai data submit tugas siswa
            $studentTask->update([
                'file_path' => null,
                'link' => null,
                'is_submitted' => false,
                'is_late' => null,
                'score' => null,
                'submitted_at' => null,
                'scored_at' => null,
                'feedback_content' => null,
                'updated_at' => Carbon::now(),
                // Kolom lain yang perlu direset ke nilai default di sini
            ]);

            return new TaskResource(true, 'Tugas siswa berhasil dihapus (direset)', []);
        } catch (ModelNotFoundException $e) {
            return new TaskResource(false, 'Tugas siswa tidak ditemukan!', null);
        } catch (\Exception $e) {
            return new TaskResource(false, 'Gagal mereset tugas siswa.', null);
        }
    }


    public function recapByClassAndSubject(Request $request)
    {
        // Mendapatkan guru yang sedang login
        $teacher = auth()->user();

        // Validasi input
        $request->validate([
            'class_id' => 'required|exists:App\Models\StudentClass,id',
        ]);

        // Ambil data dari request
        $classId = $request->input('class_id');

        // Query untuk mendapatkan tugas berdasarkan kelas dan guru yang sedang login
        $tasks = Task::where('class_id', $classId)
            ->where('creator_id', $teacher->id)
            ->with(['studentTasks' => function ($query) {
                $query->select('task_id', 'student_id', 'score');
            }])
            ->join('users', 'tasks.creator_id', '=', 'users.id')
            ->select('tasks.*', 'users.nomor_absen')
            ->get();

        // Mengatur format data yang akan dikembalikan
        $formattedTasks = $tasks->map(function ($task) {
            return [
                'no_absen' => $task->nomor_absen,
                'nama' => $task->title,
                'tugas' => $task->studentTasks->pluck('score')->toArray(),
            ];
        });

        // Membuat response dengan status dan data
        $response = [
            'status' => 'success',
            'message' => 'Tasks recap by class and subject retrieved successfully',
            'data' => $formattedTasks,
        ];

        // Mengembalikan response
        return response()->json($response);
    }


    // new function: get student file tasks from storage.
    // public function getStudentTaskFile($taskId)
    // {
    //     // Temukan tugas siswa berdasarkan ID tugas
    //     $studentTask = StudentTasks::findOrFail($taskId);

    //     // Periksa otorisasi
    //     if (Auth::user()->hasRole('guru')) {
    //         // Jika pengguna adalah guru, pastikan mereka adalah pembuat tugas
    //         if ($studentTask->task->creator_id !== Auth::user()->id) {
    //             return new TaskResource(false, 'Anda tidak diizinkan mengakses file tugas siswa ini.', null);
    //         }
    //     } elseif (Auth::user()->hasRole('siswa')) {
    //         // Jika pengguna adalah siswa, pastikan mereka adalah pemilik tugas siswa
    //         if ($studentTask->student_id !== Auth::user()->id) {
    //             return new TaskResource(false, 'Anda tidak diizinkan mengakses file tugas siswa ini.', null);
    //         }
    //     } else {
    //         // Jika pengguna bukan guru atau siswa, tolak akses
    //         return new TaskResource(false, 'Anda tidak diizinkan mengakses file tugas siswa ini.', null);
    //     }

    //     // Ambil ekstensi file
    //     $extension = File::extension(storage_path('app/public/' . $studentTask->file_path));

    //     // Tentukan tipe konten berdasarkan ekstensi file
    //     $contentType = '';

    //     switch ($extension) {
    //         case 'pdf':
    //             $contentType = 'application/pdf';
    //             break;
    //         case 'doc':
    //         case 'docx':
    //             $contentType = 'application/msword';
    //             break;
    //         case 'png':
    //             $contentType = 'image/png';
    //             break;
    //         case 'jpg':
    //         case 'jpeg':
    //             $contentType = 'image/jpeg';
    //             break;
    //         case 'zip':
    //             $contentType = 'application/zip';
    //             break;
    //         default:
    //             $contentType = 'application/octet-stream'; // Default content type
    //     }

    //     // Ambil file dari penyimpanan
    //     $fileContents = Storage::disk('public')->get($studentTask->file_path);

    //     // Atau, Anda bisa menggunakan response dengan tipe konten yang tepat
    //     return response($fileContents)
    //         ->header('Content-Type', $contentType)
    //         ->header('Content-Disposition', 'inline; filename="' . basename($studentTask->file_path) . '"');
    // }
}
