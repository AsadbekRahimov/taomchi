<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Product extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'name',
        'measure_id',
        'one_price',
        'discount_price',
        'for_telegram'
    ];

    public function measure()
    {
        return $this->belongsTo(Measure::class, 'measure_id', 'id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'product_id', 'id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'product_id', 'id');
    }
}
