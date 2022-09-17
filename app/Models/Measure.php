<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Measure extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
          'name',
          'symbol',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'measure_id', 'id');
    }
}
