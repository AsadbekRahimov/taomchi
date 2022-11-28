<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Card;
use App\Models\Order;
use App\Models\Customer;
use App\Orchid\Layouts\Sell\AddProductModal;
use App\Orchid\Layouts\Sell\CardList;
use App\Models\Product;
use App\Services\SendMessageService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MainSellScreen extends Screen
{
    public $customer;
    public $cards;
    public $ordered;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Customer $customer): iterable
    {
        $this->customer = $customer;
        $this->cards = Card::query()->where('customer_id', $customer->id)->orderByDesc('id')->get();
        $this->ordered = $this->cards->count() ? $this->cards->first()->ordered : 0;

        return [
            'products' => Product::query()->orderByDesc('id')->get(),
            'cards' => $this->cards,
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if ($this->ordered)
        {
            return 'Сотиш | ' . $this->customer->name . ' | Буюртма қабул қилинган';
        } else {
            return 'Сотиш | ' . $this->customer->name;
        }
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.sell',
        ];
    }


    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Махсулотлар')
                ->icon('barcode')
                ->modal('cardListModal')
                ->method('addSalesOrder')
                ->modalTitle('Сотилаётган махсулотлар рўйҳати')
                ->parameters([
                    'customer_id' => $this->customer->id,
                ])->canSee($this->cards->count()),
            Button::make('Ўчириш')->icon('trash')
                ->method('deleteCard')
                ->confirm('Сиз ростдан рўйҳатни ўчирмоқчимисиз?')
                ->parameters([
                    'customer_id' => $this->customer->id,
                ])->canSee($this->cards->count() && !$this->ordered),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('products', [
                TD::make('id', 'Махсулот')->render(function (Product $product) {
                    return Link::make($product->name)->href('/admin/crud/view/products/' . $product->id);
                })->cantHide(),
                TD::make('add', 'Қўшиш')->render(function (Product $product) {
                    return ModalToggle::make('')
                        ->icon('plus')
                        ->modal('addProductModal')
                        ->method('addProduct')
                        ->modalTitle($product->name . ' | Нарх: ' . number_format($product->one_price) . ' - ' . number_format($product->discount_price)  . ' сўм')
                        ->parameters([
                            'id' => $product->id,
                            'customer_id' => $this->customer->id,
                        ]);
                })->canSee(!$this->ordered)->cantHide(),
            ])->title('Махсулотлар'),
            Layout::modal('addProductModal', AddProductModal::class)
                ->size(Modal::SIZE_LG)->applyButton('Сақлаш')->closeButton('Бекор қилиш'),
            Layout::modal('cardListModal', CardList::class)
                ->size(Modal::SIZE_LG)->applyButton('Буюртма қилиш')->closeButton('Бекор қилиш')->withoutApplyButton($this->ordered),
        ];
    }

    public function addProduct(Request $request)
    {
        Card::addToCard($request);
        Alert::success('Муаффақиятли саватга қўшилди');
    }

    public function addSalesOrder(Request $request)
    {
        $order = Order::createOrder($request->customer_id);
        $cards = Card::createOrder($request->customer_id);
        SendMessageService::sendOrder($order, $cards);
        Alert::success('Буюртма муаффақиятли яратилди');
        return redirect()->route('platform.orders');
    }

    public function deleteCard(Request $request)
    {
        Card::query()->where('customer_id', $request->customer_id)->delete();
        Order::query()->where('customer_id', $request->customer_id)->delete();
        Alert::success('Муаффақиятли тозаланди');
    }
}
