<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public static function createParty($supplier_id)
    {
        $user = Auth::user();
        return self::query()->create([
            'supplier_id' => $supplier_id,
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
        ]);
    }

}
