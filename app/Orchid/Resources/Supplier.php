<?php

namespace App\Orchid\Resources;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Orchid\Crud\Filters\DefaultSorted;
use Orchid\Crud\Resource;
use Orchid\Crud\ResourceRequest;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;

class Supplier extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Supplier::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('name')->title('Исм')->required(),
                Input::make('phone')->title('Телефон рақами')
                    ->mask('(99) 999-99-99')->required(),
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
            TD::make('name', 'Исм')->cantHide(),
            TD::make('phone', 'Телефон рақам')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            })->cantHide(),
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
            Sight::make('name', 'Исм'),
            Sight::make('phone', 'Телефон рақам')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            }),
            Sight::make('created_at', 'Киритилган сана')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            Sight::make('updated_at','Ўзгартирилган сана')->render(function ($model) {
                return $model->updated_at->toDateTimeString();
            }),
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
            'phone' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Исм киритилиши шарт!',
            'phone.required' => 'Телефон рақам киритилиши шарт!',
        ];
    }

    public static function icon(): string
    {
        return 'organization';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.suppliers';
    }

    public static function label(): string
    {
        return 'Таминотчилар';
    }


    public static function description(): ?string
    {
        return 'Таминотчилар рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Киритилган сана';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги таминотчи қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги таминотчи қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Таминотчи малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Таминотчини ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Таминотчи ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Таминотчини қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Таминотчи малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги таминотчи';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Таминотчини o`zgartirish';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        $model->forceFill($request->all())->save();
        Cache::forget('suppliers');
        Cache::rememberForever('suppliers', function () {
            return \App\Models\Supplier::query()->pluck('name', 'id');
        });
    }

    public function onDelete(Model $model)
    {
        if ($model->parties()->count())
        {
            Alert::error('Сотиб олинган махсулотлар мавжудлиги учун бу таминотчини ўчира олмайсиз!');
        }elseif ($model->duties()->count())
        {
            Alert::error('Қарздорлиги мавжудлиги учун бу таминотчини ўчира олмайсиз!');
        }else {
            $model->baskets()->delete();
            $model->delete();
            Cache::forget('suppliers');
            Cache::rememberForever('suppliers', function () {
                return \App\Models\Supplier::query()->pluck('name', 'id');
            });
        }
    }
}
