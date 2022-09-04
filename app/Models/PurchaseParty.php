<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class PurchaseParty extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'branch_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'party_id', 'id');
    }

    public function expences()
    {
        return $this->hasMany(Expence::class, 'party_id', 'id');
    }

    public function duties()
    {
        return $this->hasMany(Duty::class, 'party_id', 'id');
    }

    public function purchasesSum()
    {
        $sum = 0;
        foreach ($this->purchases as $purchase) {
            $sum += ($purchase->price * $purchase->quantity);
        }
        return $sum;
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
