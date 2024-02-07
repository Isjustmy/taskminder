<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'class'
    ];


    public function students()
    {
        return $this->hasMany(User::class, 'student_class_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'class_id');
    }

}
