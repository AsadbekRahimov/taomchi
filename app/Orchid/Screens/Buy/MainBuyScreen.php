<?php

namespace App\Orchid\Screens\Buy;

use App\Models\Basket;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MainBuyScreen extends Screen
{

    public $supplier;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(Supplier $supplier): iterable
    {
        $this->supplier = $supplier;
        $branch_id = Auth::user()->branch_id ?: 0;
        return [
            'products' => Cache::remember('stock_' . $branch_id, 24 * 60 * 60 * 10, function () use ($branch_id) {
                return Stock::query()->with(['product'])->where('branch_id', $branch_id)->orderByDesc('id')->get();
            }),
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
            Layout::table('products', [
                TD::make('product_id', 'Maxsulot')->render(function (Stock $stock) {
                    return Link::make($stock->product->name)->href('/admin/crud/view/products/' . $stock->product_id);
                })->cantHide(),
                TD::make('quantity', 'Mavjud miqdori')->render(function (Stock $stock) {
                    return $stock->quantity !== 0 ? round($stock->quantity / $stock->product->box) . ' (' . $stock->quantity . ')' : 'Mavjud emas';
                })->cantHide(),
                TD::make('add', 'Qo\'shish')->render(function (Stock $stock) {
                    return ModalToggle::make('')
                        ->icon('plus')
                        ->modal('addProductModal')
                        ->method('addProduct')
                        ->modalTitle($stock->product->name)
                        ->parameters([
                            'id' => $stock->product_id,
                            'supplier_id' => $this->supplier->id,
                            'box_count' => $stock->product->box,
                        ]);
                })
            ])->title('Omborxona maxsulotlari'),
            Layout::modal('addProductModal', [
                Layout::rows([
                    Group::make([
                        CheckBox::make('box')->title('Qadoq')->sendTrueOrFalse()->value(true),
                        Input::make('quantity')->title('Miqdori')->type('number')->required(),
                        Input::make('price')->title('Narxi (xar bir dona uchun)')->type('number')->required(),
                    ]),
                ]),
            ])->size(Modal::SIZE_LG)->applyButton('Saqlash')->closeButton('Bekor qilish'),
        ];
    }

    public function addProduct(Request $request)
    {
        Basket::addToBasket($request);
        Alert::success('Muaffaqiyatli savatga qo\'shildi');
    }
    /*public function asyncProducts(string $product)
    {
        return [
            'products' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->get(),
            'max_counts' => Product::query()->where('name', 'LIKE', "%" .  $product . "%")->count(),
        ];
    }*/ // if has many products
}
