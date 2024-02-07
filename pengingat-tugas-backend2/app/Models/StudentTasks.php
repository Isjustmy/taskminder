<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTasks extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id', 'creator_id', 'task_id', 'file_path', 'link', 'is_submitted', 'score', 'scored_at', 'submitted_at', 'feedback_content', 'updated_at'
    ];

    public function tasks()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }


    public function students()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator_task()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
