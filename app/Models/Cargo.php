<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'cargo_name',
        'cargo_number',
        'container_number',
        'tracking_number',
        'category',
        'quantity',
        'measurement_id',
        'unit_type',
        'weight_cbm',
        'value',
        'origin_location_id',
        'destination_location_id',
        'transport_id',
        'packaging',
        'status',
        'special_instructions',
        'eta',
        'created_by',
        'updated_by',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }

    public function originLocation()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
