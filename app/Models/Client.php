<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Specify the table name if it's not the default plural form ('clients')
    protected $table = 'clients';

    // Allow mass assignment for these fields
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'location',
        'created_at',
        'is_repeating',
    ];

    // Define relationships if needed (e.g., a client can have many cargos)
    public function cargos()
    {
        return $this->hasMany(Cargo::class, 'client_id');
    }

    public function locations()
    {
        return $this->belongsTo(Locations::class);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(Phone_Numbers::class);
    }

    public function emails()
    {
        return $this->hasMany(Emails::class);
    }
}
