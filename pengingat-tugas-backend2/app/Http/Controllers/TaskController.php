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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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


    public function getStudentTasks()
    {
        $studentId = Auth::user()->id;
        $tasks = Task::whereHas('studentTasks', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak Ada Data Tugas.',
                'data' => null
            ], 404);
        } else {
            $additionalData = StudentTasks::where('student_id', $studentId)->get();

            // Buat array untuk menyimpan informasi tugas tambahan
            $submissionInfo = $additionalData->map(function ($studentTask) {
                return [
                    'file_path' => $studentTask->file_path ?? '-',
                    'link' => $studentTask->link ?? '-',
                    'is_submitted' => $studentTask->is_submitted ?? '-',
                    'score' => $studentTask->score ?? '-',
                    'scored_at' => $studentTask->scored_at ?? '-',
                    'submitted_at' => $studentTask->submitted_at ?? '-',
                    'feedback_content' => $studentTask->feedback_content ?? '-',
                ];
            })->filter()->values()->toArray(); // Filter null dan reset index

            // Buat array untuk menyimpan data tugas dan informasi tambahan
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
                    'submission_info' => $submissionInfo,
                ];
            });

            $responseCode = 200; // Atur kode respons sesuai kebutuhan Anda

            return response()->json($responseData, $responseCode);
        }
    }


    public function getStudentTasksByTaskId($taskId)
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


    public function all()
    {
        $all_tasks = Task::all();

        if ($all_tasks->count() > 0) {
            return new TaskResource(false, 'Semua Data Tugas', $all_tasks);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas.', null, 404);
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
        // Validasi
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:75',
            'description' => 'required|string',
            'class_id' => 'required|integer|exists:student_classes,id',
            'deadline' => 'required|date',
            'teacher_id' => 'sometimes|integer|exists:users,id',
            'file' => 'nullable|file|max:2048',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors(), 422);
        }

        // Periksa role pengguna yang sedang login
        $user = Auth::user();
        $creatorId = null;
        $guruName = null;

        if ($user->hasRole('guru')) {
            // Jika guru yang login, gunakan ID guru
            $creatorId = $user->id;
            $guruName = $user->name;
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
        }

        // Menyimpan file penugasan dari guru jika ada
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('files_from_teacher', 'public');
        }

        // Buat data tugas
        $taskData = $request->only(['title', 'description', 'class_id', 'deadline']);
        $taskData['creator_id'] = $creatorId;
        $taskData['created_at'] = Carbon::now();

        // Buat tugas
        $task = Task::create($taskData);

        // Simpan path file ke dalam kolom 'file_path' pada tabel 'tasks'
        $task->file_path = $filePath;
        $task->link = $request->link;
        $task->save();

        // Temukan kelas
        $class = StudentClass::findOrFail($request->input('class_id'));

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
            'file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:2000',
            'link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        // Dapatkan informasi siswa yang sedang masuk
        $studentId = auth()->user()->id;

        // Cari tugas berdasarkan ID
        $task = Task::findOrFail($taskId);

        // Siapkan data yang akan di-submit
        $submittedTaskData = [
            'link' => $request->input('link'),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // Tentukan apakah tugas di-submit terlambat
        $isLateSubmission = $task->deadline <= Carbon::now();

        // Jika tugas sudah pernah di-submit sebelumnya, lakukan update
        $existingSubmission = StudentTasks::where('task_id', $taskId)
            ->where('student_id', $studentId)
            ->first();

        if ($existingSubmission) {
            // Hapus file lama jika ada
            if ($request->file('file')) {
                Storage::disk('public')->delete($existingSubmission->file_path);
                $submittedTaskData['file_path'] = $request->file('file')->store('task_files', 'public');
            }

            // Update entri tugas
            $updateResult = $existingSubmission->update($submittedTaskData + ['is_late' => $isLateSubmission]);

            if ($updateResult) {
                return new TaskResource(true, 'Tugas berhasil diupdate oleh siswa', $existingSubmission);
            } else {
                return new TaskResource(false, 'Gagal update tugas.', null);
            }
        }

        // Jika tugas belum pernah di-submit sebelumnya, lakukan create
        $submittedTaskData['student_id'] = $studentId;
        $submittedTaskData['task_id'] = $taskId;

        // Simpan file baru jika ada
        if ($request->file('file')) {
            // Simpan file tugas siswa
            $file = $request->file('file');
            $filePath = $file->store('task_files', 'public');
            $submittedTaskData['file_path'] = $filePath;
        }
        $submittedTask = StudentTasks::create($submittedTaskData + ['is_late' => $isLateSubmission]);

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


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:75',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'file' => 'nullable|file|max:2048',
            'link' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors(), 422);
        }

        $user = auth()->user();

        // Check if the user is a teacher or admin
        if (!$user->hasAnyRole(['guru', 'admin', 'pengurus_kelas'])) {
            return new TaskResource(false, 'Tugas hanya dapat diedit oleh guru atau admin atau pengurus kelas.', null);
        }

        $task = Task::findOrFail($id);

        // Update task data
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->deadline = $request->input('deadline');

        // Process file if uploaded
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($task->file_path) {
                Storage::disk('public')->delete($task->file_path);
            }
            // Store new file
            $task->file_path = $request->file('file')->store('files_from_teacher', 'public');
        }

        // Update link if provided
        if ($request->has('link')) {
            $task->link = $request->input('link');
        }

        // Save changes to the task
        $task->save();

        return new TaskResource(true, 'Tugas berhasil diperbarui', $task);
    }


    public function deleteTaskFromTeacher($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Hapus file terkait jika ada
            if ($task->file_path) {
                // Dapatkan nama file dari URL
                $fileName = basename($task->file_path);

                // Hapus file dari sistem penyimpanan berdasarkan nama file
                Storage::disk('public')->delete('files_from_teacher/' . $fileName);
            }

            // Menghapus tugas berdasarkan ID yang diberikan
            $task->delete();

            return new TaskResource(true, 'Tugas berhasil dihapus', []);
        } catch (ModelNotFoundException $e) {
            return new TaskResource(false, 'Tugas tidak ditemukan!', null);
        } catch (\Exception $e) {
            return new TaskResource(false, 'Gagal menghapus tugas.', null);
        }
    }


    public function deleteTaskFromStudent($id)
    {
        try {
            $studentTask = StudentTasks::findOrFail($id);

            // Hapus file terkait jika ada
            if ($studentTask->file_path) {
                Storage::disk('public')->delete($studentTask->file_path);
            }

            // Hapus tugas siswa
            $studentTask->delete();

            return new TaskResource(true, 'Tugas siswa berhasil dihapus', []);
        } catch (ModelNotFoundException $e) {
            return new TaskResource(false, 'Tugas siswa tidak ditemukan!', null);
        } catch (\Exception $e) {
            return new TaskResource(false, 'Gagal menghapus tugas siswa.', null);
        }
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
