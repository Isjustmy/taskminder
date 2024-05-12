<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\StudentTasks;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskScore implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $teacherId;
    protected $classId;

    public function __construct($teacherId, $classId)
    {
        $this->teacherId = $teacherId;
        $this->classId = $classId;
    }

    public function collection()
    {
        $students = User::where('student_class_id', $this->classId)
                        ->orderBy('nomor_absen')
                        ->get();

        $tasks = Task::where('creator_id', $this->teacherId)
                     ->where('class_id', $this->classId)
                     ->pluck('id');

        $taskScores = [];
        if (!$tasks->isEmpty()) {
            $taskScores = StudentTasks::whereIn('task_id', $tasks)
                            ->whereIn('student_id', $students->pluck('id'))
                            ->get();
        }

        $data = [];
        foreach ($students as $student) {
            $rowData = [
                'No. Absen' => $student->nomor_absen,
                'Nama' => $student->name,
            ];

            if (!$tasks->isEmpty()) {
                foreach ($tasks as $taskId) {
                    $taskScore = $taskScores->where('student_id', $student->id)
                                            ->where('task_id', $taskId)
                                            ->first();

                    if ($taskScore && $taskScore->is_submitted) {
                        $rowData['Tugas ' . $taskId] = $taskScore->score ?: '-';
                    } else {
                        $rowData['Tugas ' . $taskId] = '-';
                    }
                }
            }

            $data[] = $rowData;
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        $headings = ['No. Absen', 'Nama'];
        $tasks = Task::where('creator_id', $this->teacherId)
                     ->where('class_id', $this->classId)
                     ->get();
        foreach ($tasks as $task) {
            $headings[] = 'Tugas ' . $task->id;
        }
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z100')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B1:B100')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [
            'A1:Z1' => ['font' => ['bold' => true], 'border' => ['outline' => ['borderStyle' => Border::BORDER_THICK]]],
            'A1:A100' => ['border' => ['left' => ['borderStyle' => Border::BORDER_THICK]]],
            'A1:Z100' => ['border' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]],
        ];
    }
}
