<?php

namespace App\Orchid\Layouts\Buy;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class PurchasePartyTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'parties';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->cantHide(),
            TD::make('user_id', 'Sotuvchi')->render(function ($model){
                return $model->user->name;
            })->cantHide(),
            TD::make('supplier_id', 'Taminotchi')->render(function ($model) {
                return $model->supplier->name;
            })->cantHide(),
            TD::make('total_price', 'Umumiy summasi')->render(function ($model){
                return Link::make(number_format($model->purchasesSum()))->type(Color::INFO());
            }),
            TD::make('total_profit', 'Umumiy foydasi')->render(function ($model){
                if ($model->purchases->sum('profit') > 0)
                {
                    return Link::make(number_format($model->purchases->sum('profit')))->type(Color::WARNING());
                }
            }),
            TD::make('expense', 'To\'langan')->render(function ($model){
                if ($model->expences->sum('price') > 0)
                    return Link::make(number_format($model->expences->sum('price')))->type(Color::SUCCESS());
            }),
            TD::make('duty', 'Qarz qolgan')->render(function ($model){
                if ($model->duties->where('supplier_id', $model->supplier_id)->sum('duty') > 0)
                    return Link::make(number_format($model->duties
                        ->where('supplier_id', $model->supplier_id)->sum('duty')))->type(Color::DANGER());
            }),
            TD::make('created_at', 'Kiritilgan sana')->render(function ($model){
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Partiya: №' . $model->id . ' | Taminotchi: ' . $model->supplier->name)
                    ->asyncParameters([
                        'purchaseParty' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}
