<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nomor_absen',
        'name',
        'email',
        'password',
        'phone_number',
        'student_class_id',
        'guru_mata_pelajaran'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // method ini di buat untuk mendapatkan list permission
    // berdasarkan user yang sedang login

    public function getPermissionArray()
    {
        return $this->getAllPermissions()->mapWithKeys(function ($pr) {
            return [$pr['name'] => true];
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function studentTasks()
    {
        return $this->hasMany(StudentTasks::class, 'student_id');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class, 'student_id');
    }

    public function studentIdentifier(): HasOne
    {
        return $this->hasOne(StudentIdentifier::class, 'student_id');
    }

    public function teacherIdentifier(): HasOne
    {
        return $this->hasOne(TeacherIdentifier::class, 'teacher_id');
    }

    public function userTokenFcm(): HasMany
    {
        return $this->hasMany(UserTokenFcm::class);
    }
    
    public function routeNotificationForFCM($notification): string|array|null
    {
         return $this->userTokenFcm()->pluck('token')->toArray();
    }

}
