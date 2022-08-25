<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'price',
        'discount',
        'user_id',
        'branch_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Customer::class, 'branch_id', 'id');
    }
}
