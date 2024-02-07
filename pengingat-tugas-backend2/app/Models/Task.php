<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'description', 'student_id', 'class_id', 'creator_id', 'mata_pelajaran', 'created_at', 'deadline'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function student()
    {   
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function task()
    {
        return $this->hasMany(StudentTasks::class, 'task_id');
    }
}
