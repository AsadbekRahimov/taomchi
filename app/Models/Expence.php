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

    public static function otherExpence($price, $description)
    {
        return self::query()->create([
            'price' => $price,
            'description' => $description,
            'branch_id' => Auth::user()->branch_id,
        ]);
    }

    public static function addFullDutyPayment($duty)
    {
        return self::query()->create([
            'price' => $duty->duty,
            'party_id' => $duty->party_id,
            'branch_id' => $duty->branch_id,
        ]);
    }

    public static function addPartDutyPayment($price, $duty)
    {
        return self::query()->create([
            'price' => $price,
            'party_id' => $duty->party_id,
            'branch_id' => $duty->branch_id,
        ]);
    }
}
