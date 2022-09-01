<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SalesParty extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;
    protected $fillable = [
        'customer_id',
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

    public static function createParty($customer_id, $discount)
    {
        $user = Auth::user();
        return self::query()->create([
            'customer_id' => $customer_id,
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'discount' => $discount,
        ]);
    }
}
