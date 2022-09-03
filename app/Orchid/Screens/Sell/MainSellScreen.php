<?php

namespace App\Orchid\Screens\Sell;

use App\Models\Card;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Customer;
use App\Orchid\Layouts\Sell\AddProductModal;
use App\Orchid\Layouts\Sell\CardList;
use App\Orchid\Layouts\Stock\ColorIndicator;
use App\Services\HelperService;
use App\Services\SendMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $branch_id = Auth::user()->branch_id ?: 0;
        $this->cards = Card::query()->where('customer_id', $customer->id)->orderByDesc('id')->get();
        //dd($this->cards->count());
        $this->ordered = $this->cards->count() ? $this->cards->first()->ordered : 0;

        return [
            'products' => Stock::query()->with(['product'])->where('branch_id', $branch_id)->orderByDesc('id')->get(),
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
            return 'Sotish | ' . $this->customer->name . ' | Buyurtma qabul qilingan';
        } else {
            return 'Sotish | ' . $this->customer->name;
        }
    }

    public function description(): ?string
    {
        return 'Omborga maxsulotlarni qabul qilish';
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
            ModalToggle::make('Maxsulotlar')
                ->icon('barcode')
                ->modal('cardListModal')
                ->method('addSalesOrder')
                ->modalTitle('Sotilayotgan maxsulotlar ro\'yhati')
                ->parameters([
                    'customer_id' => $this->customer->id,
                ])->canSee($this->cards->count()),
            Button::make('O\'chirish')->icon('trash')
                ->method('deleteCard')
                ->confirm('Siz rostdan ro\'yhatni o\'chirmoqchimisiz?')
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
            ColorIndicator::class,
            Layout::table('products', [
                TD::make('product_id', 'Maxsulot')->render(function (Stock $stock) {
                    return Link::make($stock->product->name)->href('/admin/crud/view/products/' . $stock->product_id);
                })->cantHide(),
                TD::make('box', 'Qadoqdagi soni')->render(function (Stock $stock) {
                    return $stock->product->box;
                })->cantHide(),
                TD::make('quantity', 'Mavjud miqdori')->render(function (Stock $stock) {
                    return Link::make(HelperService::getStockQuantity($stock))
                        ->type(HelperService::getStockColor($stock));
                })->cantHide(),
                TD::make('add', 'Qo\'shish')->render(function (Stock $stock) {
                    return ModalToggle::make('')
                        ->icon('plus')
                        ->modal('addProductModal')
                        ->method('addProduct')
                        ->modalTitle($stock->product->name . ' | Narx: ' . number_format($stock->product->more_price) . ' - ' . number_format($stock->product->one_price) . ' so\'m')
                        ->parameters([
                            'id' => $stock->product_id,
                            'customer_id' => $this->customer->id,
                            'box_count' => $stock->product->box,
                        ]);
                })->cantHide(),
            ])->title('Omborxona maxsulotlari'),
            Layout::modal('addProductModal', AddProductModal::class)
                ->size(Modal::SIZE_LG)->applyButton('Saqlash')->closeButton('Bekor qilish'),
            Layout::modal('cardListModal', CardList::class)
                ->size(Modal::SIZE_LG)->applyButton('Buyurtma qilish')->closeButton('Bekor qilish')->withoutApplyButton($this->ordered),
        ];
    }

    public function addProduct(Request $request)
    {
        Card::addToCard($request);
        Alert::success('Muaffaqiyatli savatga qo\'shildi');
    }

    public function addSalesOrder(Request $request)
    {
        $order = Order::createOrder($request->customer_id);
        $cards = Card::createOrder($request->customer_id);
        SendMessageService::sendOrder($order, $cards);
        Alert::success('Buyurtma muaffaqiyatli yaratildi');
        return redirect()->route('platform.orders');
    }

    public function deleteCard(Request $request)
    {
        Card::query()->where('customer_id', $request->customer_id)->delete();
        Order::query()->where('customer_id', $request->customer_id)->delete();
        Alert::success('Muaffaqiyatli tozalandi');
    }
}
