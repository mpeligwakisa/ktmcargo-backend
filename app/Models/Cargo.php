<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'cargo_number',
        'tracking_number',
        'transport_mode',
        'measure_unit',
        'measure_value',
        'total_amount',
        'paid_amount',
        'pending_amount',
        'payment_status',
        'location',
        'created_at',
    ];

    // Example relationship: Cargo belongs to a client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
