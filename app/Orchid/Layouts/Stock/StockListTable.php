<?php

namespace App\Orchid\Layouts\Stock;

use App\Models\Stock;
use App\Services\HelperService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class StockListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'stock';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('product_id', 'Maxsulot')->render(function (Stock $stock) {
                return Link::make($stock->product->name)->href('/admin/crud/view/products/' . $stock->product_id);
            })->cantHide(),
            TD::make('box', 'Qadoqdagi soni')->render(function (Stock $stock) {
                return $stock->product->box;
            })->cantHide(),
            TD::make('quantity', 'Qoldiq miqdori')->render(function (Stock $stock) {
                return ModalToggle::make(HelperService::getStockQuantity($stock))
                    ->modal('asyncEditQuantityModal')
                    ->modalTitle($stock->product->name . ': ' . $stock->quantity . ' ' . $stock->product->measure->name)
                    ->method('saveStock')
                    ->asyncParameters([
                        'stock' => $stock->id,
                    ])->type(HelperService::getStockColor($stock));
            })->cantHide(),
            TD::make('delete', '')->render(function (Stock $stock){
                return Button::make('O\'chirish')
                    ->icon('trash')
                    ->method('deleteStock')
                    ->confirm('Siz rostdan xam bu maxsulotni ombordan o\'chirmoqchimisiz?')
                    ->parameters([
                        'id' => $stock->id,
                    ]);
            }),
        ];
    }
}
