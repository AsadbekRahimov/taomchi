<?php

namespace App\Orchid\Resources;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Orchid\Crud\Filters\DefaultSorted;
use Orchid\Crud\Resource;
use Orchid\Crud\ResourceRequest;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Product::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('name')->title('Nomi')->required(),
                Select::make('measure_id')->title('O\'lchov birligi')
                    ->fromModel(\App\Models\Measure::class, 'name')->required(),
                Input::make('box')->type('number')->title('Qadoqdagi miqdori')->required(),
            ]),
            Group::make([
                Input::make('min')->type('number')->title('Ombordagi eng kam miqdori')->required(),
                Input::make('more_price')->type('number')->title('Ulgurji narx')->required(),
                Input::make('one_price')->type('number')->title('Doimiy narx')->required(),
            ]),
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
            TD::make('id'),
            TD::make('name', 'Nomi')->cantHide(),
            TD::make('measure_id', 'O\'lchov birligi')->render(function (Model $model) {
                return $model->measure->name;
            }),
            TD::make('box', 'Qadoqdagi soni'),
            TD::make('min', 'Ombordagi eng kam miqdori'),
            TD::make('more_price', 'Ulgurji narx'),
            TD::make('one_price', 'Doimiy narx'),
            TD::make('created_at', 'Kiritilgan sana')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('updated_at', 'O`zgertirilgan sana')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                })->defaultHidden(),
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
            Sight::make('name', 'Nomi'),
            Sight::make('measure_id', 'O\'lchov birligi')->render(function ($model) {
                return $model->measure->name;
            }),
            Sight::make('box', 'Qadoqdagi soni'),
            Sight::make('min', 'Ombordagi eng kam miqdori'),
            Sight::make('more_price', 'Ulgurji narx'),
            Sight::make('one_price', 'Doimiy narx'),
            Sight::make('created_at', 'Kiritilgan sana')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            Sight::make('updated_at','O`zgertirilgan sana')->render(function ($model) {
                return $model->updated_at->toDateTimeString();
            }),
        ];
    }

    public function with(): array
    {
        return ['measure'];
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
            'measure_id' => ['required'],
            'box' => ['required'],
            'min' => ['required'],
            'one_price' => ['required'],
            'more_price' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nomi kiritilishi shart!',
            'measure_id.required' => 'O\'lchov birligi',
            'box.required' => 'Qadoqdagi soni kiritilishi shart!',
            'min.required' => 'Ombordagi eng kam miqdori kiritilishi shart!',
            'more_price.required' => 'Ulgurji narx kiritilishi shart!',
            'one_price.required' => 'Doimiy narx kiritilishi shart!',
        ];
    }

    public static function icon(): string
    {
        return 'dropbox';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.products';
    }

    public static function label(): string
    {
        return 'Maxsulotlar';
    }


    public static function description(): ?string
    {
        return 'Maxsulotlar ro`yhati';
    }

    public static function singularLabel(): string
    {
        return 'Maxsulot';
    }

    public static function createButtonLabel(): string
    {
        return 'Yangi maxsulot qo`shish';
    }

    public static function createToastMessage(): string
    {
        return 'Yangi maxsulot qo`shildi';
    }

    public static function updateButtonLabel(): string
    {
        return 'O`zgartirish';
    }

    public static function updateToastMessage(): string
    {
        return 'Maxsulot malumotlari o`zgartirildi';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Maxsulotni o`chirish';
    }

    public static function deleteToastMessage(): string
    {
        return 'Maxsulot o`chirildi';
    }

    public static function saveButtonLabel(): string
    {
        return 'Saqlash';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Maxsulotni qayta tiklash';
    }

    public static function restoreToastMessage(): string
    {
        return 'Maxsulot malumotlari qayta tiklandi';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Yangi maxsulot';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Maxsulotni o`zgartirish';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Bu amallarni bajarish uchun malumotlar mavjud emas';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        if ($request->box == '0') {
            Alert::error('Qadoqdagi miqdori 0 dan katta bo\'lishi kerak!');
        } else {
            $model->forceFill($request->all())->save();
            foreach (Branch::all() as $branch) {
                Cache::forget('stock_' . $branch->id);
            }
        }
    }

    // TODO: add onDelete method
}
