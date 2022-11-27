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
                Input::make('name')->title('Номи')->required(),
                Select::make('measure_id')->title('Ўлчов бирлиги')
                    ->fromModel(\App\Models\Measure::class, 'name')->required(),
            ]),
            Group::make([
                Input::make('one_price')->type('number')->title('Чакана нарх')->required(),
                Input::make('discount_price')->type('number')->title('Чегирма нарх')->required(),
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
            TD::make('name', 'Номи')->cantHide(),
            TD::make('measure_id', 'Ўлчов бирлиги')->render(function (Model $model) {
                return $model->measure->name;
            }),
            TD::make('one_price', 'Чакана нарх'),
            TD::make('discount_price', 'Чегирма нарх'),
            TD::make('created_at', 'Киритилган сана')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                })->defaultHidden(),
            TD::make('updated_at', 'Ўзгартирилган сана')
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
            Sight::make('name', 'Номи'),
            Sight::make('measure_id', 'Ўлчов бирлиги')->render(function ($model) {
                return $model->measure->name;
            }),
            Sight::make('one_price', 'Чакана нарх'),
            Sight::make('discount_price', 'Чегирма нарх'),
            Sight::make('created_at', 'Киритилган сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            Sight::make('updated_at','Ўзгартирилган сана')->render(function ($model) {
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
            'one_price' => ['required'],
            'discount_price' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Номи киритилиши шарт!',
            'measure_id.required' => 'Ўлчов бирлиги',
            'discount_price.required' => 'Чегирма нарх киритилиши шарт!',
            'one_price.required' => 'Чакана нарх киритилиши шарт!',
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
        return 'Махсулотлар';
    }


    public static function description(): ?string
    {
        return 'Махсулотлар рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Махсулот';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги махсулот қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги махсулот қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Махсулот малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Махсулотни ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Махсулот ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Махсулотни қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Махсулот малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги махсулот';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Махсулотни ўзгартириш';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        $model->forceFill($request->all())->save();
        foreach (Branch::all() as $branch) {
            Cache::forget('stock_' . $branch->id);
        }
        Cache::forget('products');
        Cache::rememberForever('products', function () {
            return \App\Models\Product::query()->pluck('name', 'id');
        });
    }

    public function onDelete(Model $model)
    {
        if($model->sales()->count())
        {
            Alert::error('Сотилган махсулотлар мавжудлиги учун бу махсулотни ўчира олмайсиз!');
        }else {
            $model->cards()->delete();
            $model->delete();
            Cache::forget('products');
            Cache::rememberForever('products', function () {
                return \App\Models\Product::query()->pluck('name', 'id');
            });
        }
    }
}
