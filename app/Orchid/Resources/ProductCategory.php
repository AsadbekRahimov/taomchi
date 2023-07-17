<?php

namespace App\Orchid\Resources;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Orchid\Crud\Filters\DefaultSorted;
use Orchid\Crud\Resource;
use Orchid\Crud\ResourceRequest;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;

class ProductCategory extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\ProductCategory::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('name')->title('Махсулот тури номи')->required(),
        ];
    }

    /**
     * Get the columns displayed by the resource.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id')->cantHide(),
            TD::make('name', 'Махсулот тури номи')->cantHide(),
        ];
    }

    /**
     * Get the sights displayed by the resource.
     *
     * @return Sight[]
     */
    public function legend(): array
    {
        return [
            Sight::make('name', 'Махсулот тури номи'),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            new DefaultSorted('id', 'desc'),
        ];
    }

    public function rules(Model $model): array
    {
        return [
            'name' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Махсулот тури номи киритилиши шарт',
        ];
    }

    public static function icon(): string
    {
        return 'server';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.productCategory';
    }

    public static function label(): string
    {
        return 'Махсулот турлари';
    }


    public static function description(): ?string
    {
        return 'Махсулот турлари рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Махсулот тури';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги махсулот тури қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги махсулот тури қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Махсулот тури малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Махсулот турини ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Махсулот тури ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Махсулот турини қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Махсулот тури малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги махсулот тури';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Махсулот турини ўзгартириш';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        $model->forceFill($request->all())->save();
        Cache::forget('productCategories');
        CacheService::getProductCategories();
    }

    public function onDelete(Model $model)
    {
        if ($model->products()->count())
        {
            Alert::error('Бу махсулот турига бириктирилган махсулотлар мавжудлиги сабабли ўчира олмайсиз! Олдин махсулотларнинг турини ўзгартиринг.');
        }else {
            Cache::forget('productCategories');
            CacheService::getProductCategories();
        }
    }
}
