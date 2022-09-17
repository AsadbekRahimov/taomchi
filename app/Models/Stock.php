<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Stock extends Model
{
    use HasFactory;
    use AsSource, Filterable, Attachable;

    protected $fillable = [
       'product_id',
       'quantity',
       'branch_id',
    ];

    protected $allowedFilters = [
        'quantity'
    ];

    /**
     * @var array
     */
    protected $allowedSorts = [
        'quantity'
    ];

    public  const TYPE = [
        'Махсулот мавжуд' => 'Махсулот мавжуд',
        'Кам миқдорда' => 'Кам миқдорда',
        'Мавжуд емас' => 'Мавжуд емас',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public static function addNewItem(int $id, $branch_id)
    {
        return self::query()->create([
            'product_id' => $id,
            'quantity' => 0,
            'branch_id' => $branch_id,
        ]);
    }

}
