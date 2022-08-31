<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Payment extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;
    protected $fillable = [
        'customer_id',
        'price',
        'type',
        'branch_id',
    ];

    public  const TYPE = [
        1 => 'Naqt',
        2 => 'Plastik karta',
        3 => 'Cick/Payme/...',
        4 => 'Bank o\'tkazma',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
