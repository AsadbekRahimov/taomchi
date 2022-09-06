<?php

namespace App\Orchid\Resources;

use Illuminate\Database\Eloquent\Model;
use Orchid\Crud\Filters\DefaultSorted;
use Orchid\Crud\Resource;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class Measure extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Measure::class;

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
                Input::make('symbol')->title('Белгиси')->required(),
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
            TD::make('symbol', 'Белгиси')->cantHide(),
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
            Sight::make('symbol', 'Белгиси'),
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
            'symbol' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Номи киритилиши шарт!',
            'symbol.required' => 'Белгиси киритилиши шарт!',
        ];
    }

    public static function icon(): string
    {
        return 'config';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.measures';
    }

    public static function label(): string
    {
        return 'Ўлчов бирликлари';
    }


    public static function description(): ?string
    {
        return 'Ўлчов бирликлари рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Ўлчов бирлиги';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги ўлчов бирлиги қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги ўлчов бирлиги қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Ўлчов бирлиги малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Ўлчов бирлигини ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Ўлчов бирлиги ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Ўлчов бирлигини қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Ўлчов бирлиги малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги ўлчов бирлиги';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Ўлчов бирлигини o\'zgartirish';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    // TODO: add onDelete method
}
