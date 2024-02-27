<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTasks extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'student_id', 'teacher_id', 'task_id', 'file_path', 'link', 'is_submitted', 'is_late', 'score', 'scored_at', 'submitted_at', 'feedback_content', 'updated_at'
    ];

    // Accessor for file_path attribute
    public function getFilePathAttribute($value)
    {
        if ($value) {
            // If file_path exists, return the complete URL
            return asset('storage/' . $value);
        }
        // If file_path doesn't exist, return null or a default URL as per your requirement
        return null;
    }

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
