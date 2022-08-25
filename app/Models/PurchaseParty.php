<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'branch_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Customer::class, 'supplier_id', 'id');
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
