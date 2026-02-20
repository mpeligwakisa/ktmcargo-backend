<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Measurement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationships
     */

    public function cargos()
    {
        return $this->hasMany(Cargo::class);
    }
     

    // Measurement created by user
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Measurement updated by user
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
