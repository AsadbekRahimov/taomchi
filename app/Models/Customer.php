<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Customer extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
        'name',
        'phone',
        'telephone',
        'address',
        'place_id'
    ];

    protected $allowedFilters = [
        'id',
        'name',
        'place_id'
    ];

    public function duties()
    {
        return $this->hasMany(Duty::class, 'customer_id', 'id');
    }

    public function parties()
    {
        return $this->hasMany(SalesParty::class, 'customer_id', 'id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'customer_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id', 'id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id', 'id');
    }

    public function getAllNameAttribute($value)
    {
        return $this->name . ' | ' . $this->place->name . ' ' . $this->address;
    }
}
