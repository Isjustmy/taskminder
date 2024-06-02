<?php

// TaskController.php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Exports\TaskScore;
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
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{

    public function exportTaskScore($classId)
    {
        $teacherId = Auth::id();

        $studentClass = StudentClass::findOrFail($classId);
        $className = $studentClass->class;

        // Array asosiatif untuk nama bulan dalam bahasa Indonesia
        $bulanIndonesia = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        // Ambil nama bulan berdasarkan tanggal saat ini
        $bulanSekarang = date('F'); // F akan menghasilkan nama bulan dalam bahasa Inggris
        $bulanIndonesiaSekarang = $bulanIndonesia[$bulanSekarang]; // Ambil nama bulan dalam bahasa Indonesia

        // Generate nama file sesuai dengan format yang diinginkan
        $fileName = 'Rekapitulasi Tugas ' . $className . ' - ' . date('j ') . $bulanIndonesiaSekarang . date(' Y') . '.xlsx';

        return Excel::download(new TaskScore($teacherId, $classId), $fileName);
    }

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
        try {
            $teacherId = auth()->user()->id;

            // Ambil daftar tugas yang dibuat oleh guru dengan ID yang sedang masuk
            $tasks = Task::with('studentClass:id,class')
                ->where('creator_id', $teacherId)
                ->paginate(4);

            // Memformat data respons
            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file_path' => $task->file_path,
                    'link' => $task->link,
                    'creator_id' => $task->creator_id,
                    'mata_pelajaran' => $task->mata_pelajaran,
                    'deadline' => $task->deadline,
                    'created_at' => $task->created_at,
                    'class' => [
                        'id' => $task->studentClass->id,
                        'class_name' => $task->studentClass->class,
                    ],
                ];
            });

            if ($formattedTasks->isNotEmpty()) {
                $response = [
                    'success' => true,
                    'message' => 'Data Tugas Ditemukan',
                    'data' => $formattedTasks,
                    'current_page' => $tasks->currentPage(),
                    'first_page_url' => $tasks->url(1),
                    'from' => $tasks->firstItem(),
                    'last_page' => $tasks->lastPage(),
                    'last_page_url' => $tasks->url($tasks->lastPage()),
                    'next_page_url' => $tasks->nextPageUrl(),
                    'path' => $tasks->path(),
                    'per_page' => $tasks->perPage(),
                    'prev_page_url' => $tasks->previousPageUrl(),
                    'to' => $tasks->lastItem(),
                    'total' => $tasks->total(),
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Tidak Ada Data Tugas yang Ditemukan',
                    'data' => [],
                ];
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Terjadi kesalahan dalam memproses permintaan: ' . $e->getMessage(),
            ];

            return response()->json($response, 500);
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

        // Ambil data guru yang memberikan tugas
        $teacher = $task->creator()->select('id', 'name')->first();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'additional_data' => $additionalData ?? null,
            'teacher' => $teacher
        ]);
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
            'link' => 'nullable|url',
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
            'file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:2048',
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

        // Cek apakah tugas sudah disubmit dan sudah dinilai
        $existingSubmission = StudentTasks::where('task_id', $taskId)
            ->where('student_id', $studentId)
            ->first();

        if ($existingSubmission) {
            if ($existingSubmission->score !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas sudah disubmit dan sudah dinilai, tidak bisa submit ulang.',
                ], 403);
            }
        }

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
        $submittedTaskData['is_late'] = $isLateSubmission;

        // Hapus file lama jika ada
        if ($request->file('file')) {
            if ($existingSubmission && $existingSubmission->file_path) {
                $fileNameReal = basename($existingSubmission->file_path);
                Storage::disk('public')->delete('task_files/' . $fileNameReal);
            }

            $filePath = $request->file('file')->store('task_files', 'public');
            $submittedTaskData['file_path'] = $filePath;
        }

        // Simpan data tugas siswa
        $submittedTask = StudentTasks::updateOrCreate(
            ['task_id' => $taskId, 'student_id' => $studentId],
            $submittedTaskData
        );

        if ($submittedTask) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil disubmit oleh siswa',
                'data' => $submittedTask,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit tugas.',
            ], 500);
        }
    }


    public function gradeTaskByTeacher(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|integer',
            'feedback_content' => 'nullable'
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
        $isPengurusKelas = false; // Default value false, jika pengguna tidak memiliki peran Pengurus Kelas

        if ($userAuth) { // Memeriksa apakah pengguna masuk
            $userRoles = $userAuth->roles->pluck('name'); // Mendapatkan daftar peran pengguna
            $isUserGuru = $userRoles->contains('guru'); // Memeriksa apakah pengguna memiliki peran 'guru'
            $isPengurusKelas = $userRoles->contains('pengurus_kelas'); // Memeriksa apakah pengguna memiliki peran 'pengurus_kelas'
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terautentikasi.',
            ], 401);
        }

        // Jika pengguna adalah pengurus_kelas, mencegah perubahan class_id
        if ($isPengurusKelas && $request->has('class_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna dengan peran pengurus kelas tidak diperbolehkan mengubah ID kelas.',
            ], 403);
        }

        $classIdRule = $isPengurusKelas ? 'nullable' : 'required|integer|exists:student_classes,id';

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'class_id' => $classIdRule,
            'deadline' => 'required|date',
            'teacher_id' => (!$isUserGuru ? 'required|integer|exists:users,id' : 'nullable'),
            'file' => 'nullable|file|max:2048',
            'link' => 'nullable|url',
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
        $old_link = $oldTask->link;

        // Menyimpan file penugasan dari guru jika ada
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($old_taskPath) {
                $oldFile = basename($old_taskPath);
                Storage::disk('public')->delete('files_from_teacher/' . $oldFile);
            }
            // Simpan file yang baru di storage
            $filePath = $request->file('file')->store('files_from_teacher', 'public');
            $taskData['file_path'] = $filePath;
        }

        // Periksa apakah ada link dalam permintaan
        if ($request->has('link')) {
            $taskData['link'] = $request->input('link');
        }

        // Jika tugas tidak mengubah id kelas
        if ($task->class_id == $request->input('class_id')) {
            // Update tugas
            $task->update($taskData);

            // Notifikasi kepada siswa dalam kelas
            $class = StudentClass::findOrFail($task->class_id);
            $students = $class->students;
            foreach ($students as $student) {
                $student->notify(new TaskUpdatedNotification($oldTask, $task));
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
        $task->update(array_merge($taskData, ['class_id' => $request->input('class_id')]));

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

            // Periksa apakah tugas sudah disubmit
            if (!$studentTask->is_submitted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas belum disubmit, tidak ada yang perlu dihapus.',
                ], 403);
            }

            // Periksa apakah tugas sudah dinilai
            if ($studentTask->score !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas sudah dinilai, tidak bisa dihapus.',
                ], 403);
            }

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

            return response()->json([
                'success' => true,
                'message' => 'Tugas siswa berhasil dihapus (direset)',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas siswa tidak ditemukan!',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset tugas siswa.',
            ], 500);
        }
    }


    /**
     * Get tasks and their scores based on class and teacher.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recapStudentClassTask(Request $request)
    {
        $teacherId = Auth::id();

        $validateRequest = Validator::make($request->all(), [
            'class_id' => 'required|exists:student_classes,id',
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validateRequest->errors(),
            ], 422);
        }
        $classId = $request->class_id;

        $tasks = Task::where('class_id', $classId)
            ->whereHas('creator', function ($query) use ($teacherId) {
                $query->where('id', $teacherId);
            })
            ->with(['studentTasks' => function ($query) {
                $query->select('student_id', 'task_id', 'score')
                    ->with(['students' => function ($query) {
                        $query->select('id', 'nomor_absen', 'name');
                    }]);
            }])
            ->get();

        $formattedTasks = [];

        foreach ($tasks as $task) {
            $formattedTask = [
                'task_id' => $task->id,
                'judul_tugas' => $task->title,
                'deskripsi_tugas' => $task->description,
                'nilai_siswa' => []
            ];

            foreach ($task->studentTasks as $studentTask) {
                // Check if nilai_tugas is null, replace with "-"
                $nilaiTugas = $studentTask->score ?? '-';

                $formattedTask['nilai_siswa'][] = [
                    'no_absen' => $studentTask->students->nomor_absen,
                    'nama' => $studentTask->students->name,
                    'nilai_tugas' => $nilaiTugas
                ];
            }

            $formattedTasks[] = $formattedTask;
        }

        return response()->json([
            'status' => true,
            'message' => 'Data tugas dan nilainya berdasarkan kelas',
            'data' => $formattedTasks
        ]);
    }

    public function recapSpesificTask(Request $request)
    {
        $teacherId = Auth::id();

        $validateRequest = Validator::make($request->all(), [
            'class_id' => 'required|exists:student_classes,id',
            'task_id' => 'required|exists:tasks,id',
        ]);

        if ($validateRequest->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validateRequest->errors(),
            ], 422);
        }

        $taskId = $request->task_id;

        $task = Task::where('id', $taskId)
            ->whereHas('creator', function ($query) use ($teacherId) {
                $query->where('id', $teacherId); // Filter by teacherId (creator_id)
            })
            ->whereHas('studentClass', function ($query) use ($request) {
                $query->where('id', $request->class_id); // Filter by class_id
            })
            ->with(['studentTasks' => function ($query) {
                $query->select('student_id', 'task_id', 'score')
                    ->with(['students' => function ($query) {
                        $query->select('id', 'nomor_absen', 'name');
                    }]);
            }])
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan untuk guru ini pada kelas yang dimaksud',
            ], 404);
        }

        $formattedTask = [
            'id_tugas' => $task->id,
            'judul_tugas' => $task->title,
            'deskripsi_tugas' => $task->description,
            'nilai_siswa' => []
        ];

        foreach ($task->studentTasks as $studentTask) {
            // Check if nilai_tugas is null, replace with "-"
            $nilaiTugas = $studentTask->score ?? '-';

            $formattedTask['nilai_siswa'][] = [
                'no_absen' => $studentTask->students->nomor_absen,
                'nama' => $studentTask->students->name,
                'nilai_tugas' => $nilaiTugas
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Data nilai tugas siswa untuk tugas tertentu',
            'data' => $formattedTask
        ]);
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
