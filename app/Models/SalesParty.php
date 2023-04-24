<?php

namespace App\Models;

use App\Traits\HasCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SalesParty extends Model
{
    use HasFactory, HasCustomer;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'customer_id',
        'discount',
        'user_id',
        'branch_id',
        'telegram_user_id'
    ];

    protected $allowedFilters = [
        'customer_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'party_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'party_id', 'id');
    }

    public function duties()
    {
        return $this->hasMany(Duty::class, 'party_id', 'id');
    }


    public function salesSum()
    {
        $sum = 0;
        foreach ($this->sales as $sale) {
            $sum += $sale->total;
        }
        return $sum;
    }

    public static function createParty($customer_id, $discount, $telegram_user_id = null)
    {
        $user = Auth::user();
        return self::query()->create([
            'customer_id' => $customer_id,
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'discount' => $discount,
            'telegram_user_id' => $telegram_user_id
        ]);
    }
}
