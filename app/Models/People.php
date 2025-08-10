<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    //
    protected $fillable = ['first_name', 'middle_name', 'last_name', 'gender'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
