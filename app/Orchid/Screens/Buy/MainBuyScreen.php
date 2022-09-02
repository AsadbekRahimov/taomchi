<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Basket;
use App\Models\Purchase;
use App\Models\PurchaseParty;
use App\Models\Stock;
use App\Models\Supplier;
use App\Orchid\Layouts\Buy\AddProductModal;
use App\Orchid\Layouts\Buy\BasketList;
use App\Orchid\Layouts\Stock\ColorIndicator;
use App\Services\HelperService;
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
            'products' => Stock::query()->with(['product'])->where('branch_id', $branch_id)->orderByDesc('id')->get(),
            'baskets' => $this->baskets,
        ];
    }



    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Sotib olish | ' . $this->supplier->name;
    }

    public function description(): ?string
    {
        return 'Omborga maxsulotlarni qabul qilish';
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
            ModalToggle::make('Maxsulotlar')
                ->icon('barcode')
                ->modal('basketListModal')
                ->method('addPurchaseParty')
                ->modalTitle('Sotib olinayotgan maxsulotlar ro\'yhati')
                ->parameters([
                    'supplier_id' => $this->supplier->id,
                ])->canSee($this->baskets->count()),
            Button::make('O\'chirish')->icon('trash')
                ->method('deleteBasket')
                ->confirm('Siz rostdan ro\'yhatni o\'chirmoqchimisiz?')
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
                        ->modalTitle($stock->product->name . ' | Sotish: ' . number_format($stock->product->more_price) . ' so\'m')
                        ->parameters([
                            'id' => $stock->product_id,
                            'supplier_id' => $this->supplier->id,
                            'box_count' => $stock->product->box,
                        ]);
                })->cantHide(),
            ])->title('Omborxona maxsulotlari'),
            Layout::modal('addProductModal', AddProductModal::class)
                ->size(Modal::SIZE_LG)->applyButton('Saqlash')->closeButton('Bekor qilish'),
            Layout::modal('basketListModal', BasketList::class)
                ->size(Modal::SIZE_LG)->applyButton('Saqlash')->closeButton('Bekor qilish'),
        ];
    }

    public function addProduct(Request $request)
    {
        Basket::addToBasket($request);
        Alert::success('Muaffaqiyatli savatga qo\'shildi');
    }

    public function addPurchaseParty(Request $request)
    {
        $party = PurchaseParty::createParty($request->supplier_id);
        $this->deleteBasket($request);
        Purchase::createPurchases($party, $request->baskets);
        Alert::success('Maxsulotlar muaffaqiyatli omborga qo\'shildi');
        return redirect()->route('platform.buy_parties');
    }

    public function deleteBasket(Request $request)
    {
        Basket::query()->where('supplier_id', $request->supplier_id)->delete();
        Alert::success('Muaffaqiyatli tozalandi');
    }

    /*public function asyncProducts(string $product)
    {
        return [
            'products' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->get(),
            'max_counts' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->count(),
        ];
    }*/ // if has many products
}
