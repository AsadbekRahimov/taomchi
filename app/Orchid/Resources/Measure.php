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
                Input::make('name')->title('Nomi')->required(),
                Input::make('symbol')->title('Belgisi')->required(),
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
            TD::make('name', 'Ism')->cantHide(),
            TD::make('symbol', 'Belgisi')->cantHide(),
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
            Sight::make('name', 'Ism'),
            Sight::make('symbol', 'Belgisi'),
            Sight::make('created_at', 'Kiritilgan sana')->render(function ($model) {
                return $model->created_at->toDateTimeString();
            }),
            Sight::make('updated_at','O`zgertirilgan sana')->render(function ($model) {
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
            'name.required' => 'Nomi kiritilishi shart!',
            'symbol.required' => 'Belgisi kiritilishi shart!',
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
        return 'O\'lchov birliklari';
    }


    public static function description(): ?string
    {
        return 'O\'lchov birliklari ro\'yhati';
    }

    public static function singularLabel(): string
    {
        return 'O\'lchov birligi';
    }

    public static function createButtonLabel(): string
    {
        return 'Yangi o\'lchov birligi qo\'shish';
    }

    public static function createToastMessage(): string
    {
        return 'Yangi o\'lchov birligi qo`shildi';
    }

    public static function updateButtonLabel(): string
    {
        return 'O\'zgartirish';
    }

    public static function updateToastMessage(): string
    {
        return 'O\'lchov birligi malumotlari o\'zgartirildi';
    }

    public static function deleteButtonLabel(): string
    {
        return 'O\'lchov birligini o\'chirish';
    }

    public static function deleteToastMessage(): string
    {
        return 'O\'lchov birligi o`chirildi';
    }

    public static function saveButtonLabel(): string
    {
        return 'Saqlash';
    }

    public static function restoreButtonLabel(): string
    {
        return 'O\'lchov birligini qayta tiklash';
    }

    public static function restoreToastMessage(): string
    {
        return 'O\'lchov birligi malumotlari qayta tiklandi';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Yangi o\'lchov birligi';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'O\'lchov birligini o\'zgartirish';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Bu amallarni bajarish uchun malumotlar mavjud emas';
    }

    // TODO: add onDelete method
}
