<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'people_id',
        'phone',
        'role_id',
        'location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            
            'password' => 'hashed',
        ];
    }

    /**
     * Automatically set the "name" when creating a new user.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Fetch related people record (since first_name & last_name are in people table)
            if ($user->people) {
                $firstInitial = strtoupper(substr($user->people->first_name, 0, 1));
                $lastName = ucfirst($user->people->last_name);
                $user->name = $firstInitial . $lastName;
            }
        });
    }

    public function people()
    {
        return $this->belongsTo(People::class, 'people_id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(Phone_Numbers::class);
    }

    public function role()
    {
    return $this->belongsTo(Role::class, 'role_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // App\Models\User.php
    public function isAdmin()
    {
        return in_array($this->role->name, ['Admin', 'Superuser']); 
    }



}
