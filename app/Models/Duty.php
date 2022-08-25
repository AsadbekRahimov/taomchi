<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Duty extends Model
{
    use HasFactory;

    protected $fillable = [
       'customer_id',
       'supplier_id',
       'duty',
       'date',
       'branch_id',
       'party_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Customer::class, 'branch_id', 'id');
    }

    public function sales()
    {
        return $this->belongsTo(SalesParty::class, 'party_id', 'id');
    }

    public function purchases()
    {
        return $this->belongsTo(PurchaseParty::class, 'party_id', 'id');
    }
}
