<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Basket;
use App\Models\Duty;
use App\Models\Expence;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\Stock;
use App\Models\Supplier;
use App\Orchid\Layouts\Buy\AddProductModal;
use App\Orchid\Layouts\Buy\BasketList;
use App\Orchid\Layouts\FilterSelections\StockSelection;
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

class MainBuyScreen extends Screen
{

    public $supplier;
    public $baskets;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Supplier $supplier): iterable
    {
        $this->supplier = $supplier;
        $branch_id = Auth::user()->branch_id ?: 0;
        $this->baskets = Basket::query()->where('supplier_id', $supplier->id)->orderByDesc('id')->get();
        return [
            'products' => Stock::query()->filters(StockSelection::class)->with(['product'])->where('branch_id', $branch_id)->orderByDesc('id')->get(),
            'baskets' => $this->baskets,
            'total_price' => Basket::getTotalPrice($supplier->id),
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Сотиб олиш | ' . $this->supplier->name;
    }

    public function description(): ?string
    {
        return 'Омборга махсулотларни қабул қилиш';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.stock.buy',
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
                ->modal('basketListModal')
                ->method('addPurchaseParty')
                ->modalTitle('Сотиб олинаётган махсулотлар рўйҳати')
                ->parameters([
                    'supplier_id' => $this->supplier->id,
                ])->canSee($this->baskets->count()),
            Button::make('Ўчириш')->icon('trash')
                ->method('deleteBasket')
                ->confirm('Сиз ростдан рўйҳатни ўчирмоқчимисиз?')
                ->parameters([
                    'supplier_id' => $this->supplier->id,
                ])->canSee($this->baskets->count()),
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
            //ProductListener::class, --> if has many products
            ColorIndicator::class,
            StockSelection::class,
            Layout::table('products', [
                TD::make('product_id', 'Махсулот')->render(function (Stock $stock) {
                    return Link::make($stock->product->name)->href('/admin/crud/view/products/' . $stock->product_id);
                })->cantHide(),
                TD::make('box', 'Қадоқдаги сони')->render(function (Stock $stock) {
                    return $stock->product->box;
                })->cantHide(),
                TD::make('quantity', 'Мавжуд миқдори')->render(function (Stock $stock) {
                    return Link::make(HelperService::getStockQuantity($stock))
                        ->type(HelperService::getStockColor($stock));
                })->cantHide(),
                TD::make('add', 'Қўшиш')->render(function (Stock $stock) {
                    return ModalToggle::make('')
                        ->icon('plus')
                        ->modal('addProductModal')
                        ->method('addProduct')
                        ->modalTitle($stock->product->name . ' | Сотиш: ' . number_format($stock->product->more_price) . ' сўм')
                        ->parameters([
                            'id' => $stock->product_id,
                            'supplier_id' => $this->supplier->id,
                            'box_count' => $stock->product->box,
                        ]);
                })->cantHide(),
            ])->title('Омборхона махсулотлари'),
            Layout::modal('addProductModal', AddProductModal::class)
                ->size(Modal::SIZE_LG)->applyButton('Сақлаш')->closeButton('Бекор қилиш'),
            Layout::modal('basketListModal', BasketList::class)
                ->size(Modal::SIZE_LG)->applyButton('Сақлаш')->closeButton('Бекор қилиш'),
        ];
    }

    public function addProduct(Request $request)
    {
        Basket::addToBasket($request);
        Alert::success('Муаффақиятли саватга қўшилди');
    }

    public function addPurchaseParty(Request $request)
    {
        $all_price = Basket::getTotalPrice($request->supplier_id);
        if ((int)$request->total_price > $all_price){
            Alert::error('Умумий тўлов суммаси махсулотлар суммасидан кўп бо\ълмаслиги керак!');
        } else {
            $party = PurchaseParty::createParty($request->supplier_id);
            $this->deleteBasket($request);
            Purchase::createPurchases($party, $request->baskets);
            if ((int)$request->total_price < $all_price)
            {
                $expence = Expence::purchaseExpence($party->id, $request->total_price, $party->branch_id);
                $duty = Duty::purchaseDuty($request->supplier_id, $party->id, $all_price - $request->total_price, $party->branch_id);
                SendMessageService::sendPurchase($party, $expence->price, $duty->duty);
            } else { // when total_price = all_price
                $expence = Expence::purchaseExpence($party->id, $all_price, $party->branch_id);
                SendMessageService::sendPurchase($party, $expence->price, 0);
            }

            Alert::success('Махсулотлар муаффақиятли омборга қўшилди');
            return redirect()->route('platform.buy_parties');
        }
    }

    public function deleteBasket(Request $request)
    {
        Basket::query()->where('supplier_id', $request->supplier_id)->delete();
        Alert::success('Муаффақиятли тозаланди');
    }

    /*public function asyncProducts(string $product)
    {
        return [
            'products' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->get(),
            'max_counts' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->count(),
        ];
    }*/ // if has many products
}
