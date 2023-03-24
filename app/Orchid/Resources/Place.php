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

class Place extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Place::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('name')->title('Худуд номи')->required(),
            Input::make('telegram_message_id')->title('Telegram ID')->required(),
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
            TD::make('name', 'Худуд номи')->cantHide(),
            TD::make('telegram_message_id', 'Telegram ID')->cantHide(),
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
            Sight::make('name', 'Худуд номи'),
            Sight::make('telegram_message_id', 'Telegram ID'),
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
            'telegram_message_id' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Худуд номи киритилиши шарт',
            'telegram_message_id.required' => 'Telegram ID киритилиши шарт',
        ];
    }

    public static function icon(): string
    {
        return 'modules';
    }

    public static function perPage(): int
    {
        return 15;
    }

    public static function permission(): ?string
    {
        return 'platform.special.places';
    }

    public static function label(): string
    {
        return 'Худудлар';
    }


    public static function description(): ?string
    {
        return 'Худудлар рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Худуд';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги худуд қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги худуд қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Худуд малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Худудни ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Худуд ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Худудни қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Худуд малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги худуд';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Худудни ўзгартириш';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        $model->forceFill($request->all())->save();
        Cache::forget('places');
        CacheService::getPlaces();
    }

    public function onDelete(Model $model)
    {
        if ($model->customers()->count())
        {
            Alert::error('Бу худудга бириктирилган мижозлар мавжудлиги сабабли худудни ўчира олмайсиз! Олдин мижозларнинг худудини ўзгартиринг.');
        }else {
            Cache::forget('places');
            CacheService::getPlaces();
        }
    }
}
