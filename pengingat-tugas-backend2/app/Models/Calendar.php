<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'date_marker', 'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

}
