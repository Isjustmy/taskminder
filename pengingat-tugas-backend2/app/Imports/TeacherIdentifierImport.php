<?php

namespace App\Imports;

use App\Models\TeacherIdentifier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

class TeacherIdentifierImport implements ToModel
{
    /**
     * @param array $row
     * @return TeacherIdentifier|null
     */
    public function model(array $row)
    {
        return new TeacherIdentifier([
            'nip' => $row['NIP'], // Assuming 'nip' is the correct column name in your Excel file
            // You may need to map other attributes or retrieve 'teacher_id' based on your application logic
        ]);
    }
}