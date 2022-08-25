<?php

namespace App\Orchid\Resources;

use App\Services\HelperService;
use Illuminate\Database\Eloquent\Model;
use Orchid\Crud\Filters\DefaultSorted;
use Orchid\Crud\Resource;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class Customer extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Customer::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Group::make([
                Input::make('name')->title('Ism')->required(),
                Input::make('phone')->title('Telefon raqami')->mask('(99) 999-99-99')->required(),
                Input::make('address')->title('Manzili')->required(),
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
            TD::make('phone', 'Telefon raqam')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            })->cantHide(),
            TD::make('address', 'Manzili'),
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
            Sight::make('phone', 'Telefon raqam')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            }),
            Sight::make('address', 'Manzili'),
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
            'phone' => ['required'],
            'address' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ism kiritilishi shart!',
            'phone.required' => 'Telefon raqam kiritilishi shart!',
            'address.required' => 'Manzili kiritilishi shart!',
        ];
    }

    public static function icon(): string
    {
        return 'people';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.customers';
    }

    public static function label(): string
    {
        return 'Mijozlar';
    }


    public static function description(): ?string
    {
        return 'Mijozlar ro`yhati';
    }

    public static function singularLabel(): string
    {
        return 'Mijoz';
    }

    public static function createButtonLabel(): string
    {
        return 'Yangi mijoz qo`shish';
    }

    public static function createToastMessage(): string
    {
        return 'Yangi mijoz qo`shildi';
    }

    public static function updateButtonLabel(): string
    {
        return 'O`zgartirish';
    }

    public static function updateToastMessage(): string
    {
        return 'Mijoz malumotlari o`zgartirildi';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Mijozni o`chirish';
    }

    public static function deleteToastMessage(): string
    {
        return 'Mijoz o`chirildi';
    }

    public static function saveButtonLabel(): string
    {
        return 'Saqlash';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Mijozni qayta tiklash';
    }

    public static function restoreToastMessage(): string
    {
        return 'Mijoz malumotlari qayta tiklandi';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Yangi mijoz';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Mijozni o`zgartirish';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Bu amallarni bajarish uchun malumotlar mavjud emas';
    }

    // TODO: add onDelete method

}
