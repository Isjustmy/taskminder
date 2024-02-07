<?php

// TaskController.php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Models\StudentTasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{

    public function taskSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:student_classes,id',
            'mata_pelajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validation failed', $validator->errors());
        }

        $classId = $request->input('class_id');
        $subject = $request->input('mata_pelajaran');

        // Get all students in a specific class
        $students = User::where('student_class_id', $classId)->get();

        // Get all tasks for a specific subject and class
        $tasks = Task::where('class_id', $classId)
            ->where('mata_pelajaran', $subject)
            ->with(['student', 'tasks'])
            ->get();

        // Create dynamic column headers
        $header = ['Nama Siswa'];
        foreach ($tasks as $task) {
            $header[] = $task->title; // Use task title as the column header
        }

        // Create summary data
        $summaryData = [$header]; // Column headers

        foreach ($students as $student) {
            $row = [$student->name]; // Student name

            foreach ($tasks as $task) {
                // Get student's task score for each task
                $score = $task->tasks->where('student_id', $student->id)->first()->score ?? '-';
                $row[] = $score;
            }

            $summaryData[] = $row;
        }

        return new TaskResource(true, 'Task Summary', $summaryData);
    }

    public function index()
    {
        $studentId = Auth::user()->id;
        $tasks = Task::where('student_id', $studentId)->get();

        if ($tasks->count() > 0) {
            return new TaskResource(true, 'Data Tugas Anda', $tasks);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas.', null);
        }
    }

    public function all()
    {
        $all_tasks = Task::all();

        if ($all_tasks->count() > 0) {
            return new TaskResource(true, 'Data Tugas', $all_tasks);
        } else {
            return new TaskResource(false, 'Tidak Ada Data Tugas.', null);
        }
    }

    public function show($id)
    {
        $studentId = Auth::user()->id;
        $task = Task::where('id', $id)
            ->where('student_id', $studentId)
            ->firstOrFail();

        if ($task) {
            return new TaskResource(true, 'Detail Data Tugas Berdasarkan ID', $task);
        } else {
            return new TaskResource(false, 'Detail Data Tugas Tidak Ditemukan.', null);
        }
    }

    // kode untuk membuat tugas untuk satu siswa saja
    public function createTaskForStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:75',
            'description' => 'required|string',
            'student_id' => 'required|exists:users,id',
            'deadline' => 'required|date',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        $creatorId = auth()->user()->id;

        // Get the user with the specified 'student_id'
        $student = User::find($request->input('student_id'));

        // Check if the user has the 'siswa' role
        if ($student && $student->hasRole('siswa')) {
            $taskData = $request->all();
            $taskData['creator_id'] = $creatorId;
            $taskData['created_at'] = Carbon::now();

            $task = Task::create($taskData);

            if ($task) {

                $student->notify(new TaskNotification($task, auth()->user()->name));

                return new TaskResource(true, 'Tugas berhasil dibuat untuk siswa', $task);
            } else {
                return new TaskResource(false, 'Gagal membuat tugas.', null);
            }
        } else {
            return new TaskResource(false, 'User dengan student_id tidak ditemukan atau bukan siswa.', null);
        }
    }

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
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        // Periksa role pengguna yang sedang login
        $user = Auth::user();
        $creatorId = null;

        if ($user->hasRole('guru')) {
            // Jika guru yang login, gunakan ID guru
            $creatorId = $user->id;
        } elseif ($user->hasAnyRole(['pengurus_kelas', 'admin'])) {
            // Jika pengurus_kelas atau admin yang membuat tugas
            $guruIdFromInput = $request->input('teacher_id');

            if ($guruIdFromInput) {
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
            } else {
                // Tampilkan pesan atau instruksi untuk memasukkan ID guru
                return new TaskResource(false, 'Silakan masukkan ID guru yang akan menetapkan tugas.', null);
            }
        }

        // Buat data tugas
        $tasksData = $request->all();
        $tasksData['creator_id'] = $creatorId;
        $tasksData['created_at'] = Carbon::now();

        // Find all students in the specified class
        $students = User::whereHas('studentClass', function ($query) use ($request) {
            $query->where('id', $request->input('class_id'));
        })->get();

        if ($students->count() > 0) {
            $tasks = [];
            foreach ($students as $student) {
                $tasksData['student_id'] = $student->id;
                $tasksData['class_id'] = $student->studentClass->id;
                $task = Task::create($tasksData);
                $tasks[] = $task;

                $student->notify(new TaskNotification($tasks[0], $guruName));
            }

            return new TaskResource(true, 'Tugas berhasil dibuat untuk kelas', $tasks);
        } else {
            return new TaskResource(false, 'Tidak ada siswa dalam kelas tersebut.', null);
        }
    }


    public function submitTaskByStudent(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,zip|max:2000',
            'link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        $studentId = auth()->user()->id;
        $task = Task::findOrFail($taskId);

        // Pastikan tugas belum pernah di-submit oleh siswa sebelumnya
        $existingSubmission = StudentTasks::where('task_id', $taskId)
            ->where('student_id', $studentId)
            ->first();

        // Validasi: Tugas hanya dapat diubah sebelum deadline
        if ($existingSubmission && $task->deadline <= Carbon::now()) {
            return new TaskResource(false, 'Tugas tidak dapat diubah setelah deadline.', null);
        }

        $submittedTaskData = [
            'student_id' => $studentId,
            'task_id' => $taskId,
            'file_path' => $request->file('file')->store('task_files', 'public'),
            'link' => $request->input('link'),
            'is_submitted' => true,
            'submitted_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // Jika tugas sudah pernah di-submit, lakukan update
        if ($existingSubmission) {
            $updateResult = $existingSubmission->update($submittedTaskData);

            if ($updateResult) {
                return new TaskResource(true, 'Tugas berhasil diupdate oleh siswa', $existingSubmission);
            } else {
                return new TaskResource(false, 'Gagal update tugas.', null);
            }
        } else {
            // Jika tugas belum pernah di-submit, lakukan create
            $submittedTask = StudentTasks::create($submittedTaskData);

            if ($submittedTask) {
                return new TaskResource(true, 'Tugas berhasil disubmit oleh siswa', $submittedTask);
            } else {
                return new TaskResource(false, 'Gagal submit tugas.', null);
            }
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
        ]);

        if ($validator->fails()) {
            return new TaskResource(false, 'Validasi gagal', $validator->errors());
        }

        $user = auth()->user();

        // Check if the user is a teacher or admin
        if (!$user->hasAnyRole(['guru', 'admin'])) {
            return new TaskResource(false, 'Tugas hanya dapat diedit oleh guru atau admin.', null);
        }

        $task = Task::findOrFail($id);

        $task->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'deadline' => $request->input('deadline'),
        ]);

        if ($task) {
            return new TaskResource(true, 'Tugas berhasil diperbarui', $task);
        } else {
            return new TaskResource(false, 'Gagal memperbarui tugas.', null);
        }
    }


    public function deleteTaskFromTeacher($id)
    {
        try {
            $task = Task::findOrFail($id);

            // Hapus terlebih dahulu student_tasks terkait
            $task->task()->delete();

            // Hapus file terkait jika ada
            if ($task->file_path) {
                Storage::disk('public')->delete($task->file_path);
            }

            // Hapus tugas
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
}
