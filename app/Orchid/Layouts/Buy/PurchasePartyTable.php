<?php

namespace App\Orchid\Layouts\Buy;

use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Select;
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
            TD::make('user_id', 'Сотувчи')->render(function ($model){
                return $model->user->name;
            })->cantHide(),
            TD::make('supplier_id', 'Таминотчи')->render(function ($model) {
                return Link::make($model->supplier->name)->route('platform.supplier_info', ['supplier' => $model->supplier_id]);
            })->filter(Select::make('supplier_id')->options(Cache::get('suppliers'))->empty('', ''))->cantHide(),
            TD::make('total_price', 'Умумий суммаси')->render(function ($model){
                return Link::make(number_format($model->purchasesSum()))->type(Color::INFO());
            }),
            TD::make('total_profit', 'Умумий фойдаси')->render(function ($model){
                if ($model->purchases->sum('profit') > 0)
                {
                    return Link::make(number_format($model->purchases->sum('profit')))->type(Color::WARNING());
                }
            }),
            TD::make('expense', 'Тўланган')->render(function ($model){
                if ($model->expences->sum('price') > 0)
                    return Link::make(number_format($model->expences->sum('price')))->type(Color::SUCCESS());
            }),
            TD::make('duty', 'Қарз қолган')->render(function ($model){
                if ($model->duties->where('supplier_id', $model->supplier_id)->sum('duty') > 0)
                    return Link::make(number_format($model->duties
                        ->where('supplier_id', $model->supplier_id)->sum('duty')))->type(Color::DANGER());
            }),
            TD::make('created_at', 'Киритилган сана')->render(function ($model){
                return $model->created_at->toDateTimeString();
            })->cantHide(),
            TD::make('')->render(function ($model){
                return ModalToggle::make('')
                    ->icon('eye')
                    ->modal('asyncGetPartyModal')
                    ->modalTitle('Партия: №' . $model->id . ' | Таминотчи: ' . $model->supplier->name)
                    ->asyncParameters([
                        'purchaseParty' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}
