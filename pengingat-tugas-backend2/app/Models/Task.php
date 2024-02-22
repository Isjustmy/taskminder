<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title', 'description', 'file_path', 'link', 'class_id', 'creator_id', 'mata_pelajaran', 'created_at', 'deadline'
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function studentTasks(): HasMany
    {
        return $this->hasMany(StudentTasks::class, 'task_id');
    }
}
