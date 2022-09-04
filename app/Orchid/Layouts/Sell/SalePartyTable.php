<?php

namespace App\Orchid\Layouts\Sell;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class SalePartyTable extends Table
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
            TD::make('supplier_id', 'Mijoz')->render(function ($model) {
                return $model->customer->name;
            })->cantHide(),
            TD::make('total_price', 'Umumiy summasi')->render(function ($model){
                return Link::make(number_format($model->salesSum()))->type(Color::INFO());
            }),
            TD::make('discount', 'Chegirma')->render(function ($model){
                if ($model->discount > 0)
                    return Link::make(number_format($model->discount))->type(Color::WARNING());
            }),
            TD::make('payments', 'To\'lagan')->render(function ($model){
                if ($model->payments->sum('price') > 0)
                    return Link::make(number_format($model->payments->sum('price')))->type(Color::SUCCESS());
            }),
            TD::make('duty', 'Qarz bo\'lgan')->render(function ($model){
                if ($model->duties->where('customer_id', $model->customer_id)->sum('duty') > 0)
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
                    ->modalTitle('Partiya: №' . $model->id . ' | Mijoz: ' . $model->customer->name)
                    ->asyncParameters([
                        'salesParty' => $model->id,
                    ]);
            })->cantHide(),
        ];
    }
}
