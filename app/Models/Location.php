<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    //
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'code',
        'description',

    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function p()
    {
        return $this->hasMany(Phone_Numbers::class);
    }
}
