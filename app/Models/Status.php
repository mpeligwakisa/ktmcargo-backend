<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //
    protected $table = 'status'; // 👈 tell Laravel to use the singular table
    
    protected $fillable = ['description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
