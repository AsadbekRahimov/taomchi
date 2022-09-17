<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Supplier extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'name',
        'phone',
    ];

    public function baskets()
    {
        return $this->hasMany(Basket::class, 'supplier_id', 'id');
    }

    public function parties()
    {
        return $this->hasMany(PurchaseParty::class, 'supplier_id', 'id');
    }

    public function duties()
    {
        return $this->hasMany(Duty::class, 'supplier_id', 'id');
    }
}
