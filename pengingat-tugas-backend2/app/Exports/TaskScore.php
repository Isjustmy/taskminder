<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\StudentClass;
use App\Models\StudentTasks;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskScore implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $teacherId;
    protected $classId;

    public function __construct($teacherId, $classId)
    {
        // Mendapatkan ID guru yang sedang login
        $this->teacherId = $teacherId;
        
        // Menggunakan class_id yang diberikan melalui parameter input
        $this->classId = $classId;
    }

    /**
    * @return Collection
    */
    public function collection()
    {
        // Mendapatkan tugas yang diberikan oleh guru yang sedang login
        $tasks = Task::where('creator_id', $this->teacherId)->pluck('id');

        // Mendapatkan data siswa berdasarkan class_id yang diberikan
        $students = User::where('student_class_id', $this->classId)
                        ->orderBy('nomor_absen')
                        ->get();

        // Mendapatkan nilai tugas siswa yang sesuai dengan tugas yang diberikan oleh guru
        $taskScores = StudentTasks::whereIn('task_id', $tasks)
                        ->whereIn('student_id', $students->pluck('id'))
                        ->get();

        // Mengumpulkan data nilai tugas siswa sesuai format yang diminta
        $data = [];
        foreach ($students as $student) {
            $rowData = [
                'No. Absen' => $student->nomor_absen,
                'Nama' => $student->name,
            ];

            foreach ($tasks as $taskId) {
                $taskScore = $taskScores->where('student_id', $student->id)
                                        ->where('task_id', $taskId)
                                        ->first();
                $rowData['Tugas' . $taskId] = $taskScore ? $taskScore->score : '-';
            }

            $data[] = $rowData;
        }

        return new Collection($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Membuat judul kolom sesuai format yang diminta
        $headings = ['No. Absen', 'Nama'];
        $tasks = Task::where('creator_id', $this->teacherId)->get();
        foreach ($tasks as $task) {
            $headings[] = 'Tugas ' . $task->id;
        }
        return $headings;
    }

    /**
     * CATATAN: styling tabel excel ini masih belum bekerja dengan benar.
     * 
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Menambahkan border pada tabel
            'A1:Z1' => ['font' => ['bold' => true], 'border' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]], // Border tebal di sisi luar tabel dan bold font pada baris pertama (judul)
            'A1:A100' => ['border' => ['left' => ['borderStyle' => Border::BORDER_THICK]]], // Border tebal di sebelah kiri
            'A1:Z100' => ['border' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]], // Border tipis di sisi dalam tabel
        ];
    }
}
