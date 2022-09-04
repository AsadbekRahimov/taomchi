<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Expence extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'price',
        'description',
        'party_id',
        'branch_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function party()
    {
        return $this->belongsTo(PurchaseParty::class, 'party_id', 'id');
    }


    public static function purchaseExpence($id, $total_price, $branch_id)
    {
        return self::query()->create([
            'price' => $total_price,
            'party_id' => $id,
            'branch_id' => $branch_id,
        ]);
    }

    public static function otherExpence($price, $description)
    {
        return self::query()->create([
            'price' => $price,
            'description' => $description,
            'branch_id' => Auth::user()->branch_id,
        ]);
    }
}
