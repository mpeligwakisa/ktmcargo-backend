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

    // Specify the table name if it's not the default plural form ('locations')
    protected $table = 'locations';
    
    protected $fillable = [
        'name',
        'code',
        'description',

    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function cargo()
    {
        return $this->hasMany(Cargo::class, 'location_id');
    }

    public function phoneNumbers()
    {
        return $this->hasMany(Phone_Numbers::class);
    }
}
