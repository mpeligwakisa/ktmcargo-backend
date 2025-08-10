<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Specify the table name if it's different from the default ('payments')
    protected $table = 'payments';

    // Allow mass assignment for the following fields
    protected $fillable = [
        'cargo_id',
        'client_id',
        'amount_paid',
        'payment_status', // PAID, PARTIAL, or UNPAID
        'payment_method',
        'transaction_id',
        'payment_date',
    ];

    // Relationships (Optional)
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
