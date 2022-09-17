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
                Input::make('name')->title('Исм')->required(),
                Input::make('address')->title('Манзили')->required(),
            ]),
            Group::make([
                Input::make('phone')->title('Телефон рақами 1')->mask('(99) 999-99-99')->required(),
                Input::make('telephone')->title('Телефон рақами 2')->mask('(99) 999-99-99'),
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
            TD::make('phone', 'Телефон рақам 1')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            })->cantHide(),
            TD::make('telephone', 'Телефон рақам 2')->render(function ($model) {
                return Link::make($model->telephone)->href('tel:' . HelperService::telephone($model->telephone));
            })->cantHide(),
            TD::make('address', 'Манзили'),
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
            Sight::make('phone', 'Телефон рақам 1 ')->render(function ($model) {
                return Link::make($model->phone)->href('tel:' . HelperService::telephone($model->phone));
            }),
            Sight::make('telephone', 'Телефон рақам 2')->render(function ($model) {
                return Link::make($model->telephone)->href('tel:' . HelperService::telephone($model->telephone));
            }),
            Sight::make('address', 'Манзили'),
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
            'address' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Исм киритилиши шарт',
            'phone.required' => 'Телефон рақам киритилиши шарт!',
            'address.required' => 'Манзили киритилиши шарт!',
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
        return 'Мижозлар';
    }


    public static function description(): ?string
    {
        return 'Мижозлар рўйҳати';
    }

    public static function singularLabel(): string
    {
        return 'Мижоз';
    }

    public static function createButtonLabel(): string
    {
        return 'Янги мижоз қўшиш';
    }

    public static function createToastMessage(): string
    {
        return 'Янги мижоз қўшилди';
    }

    public static function updateButtonLabel(): string
    {
        return 'Ўзгартириш';
    }

    public static function updateToastMessage(): string
    {
        return 'Мижоз малумотлари ўзгартирилди';
    }

    public static function deleteButtonLabel(): string
    {
        return 'Мижозни ўчириш';
    }

    public static function deleteToastMessage(): string
    {
        return 'Мижоз ўчирилди';
    }

    public static function saveButtonLabel(): string
    {
        return 'Сақлаш';
    }

    public static function restoreButtonLabel(): string
    {
        return 'Мижозни қайта тиклаш';
    }

    public static function restoreToastMessage(): string
    {
        return 'Мижоз малумотлари қайта тикланди';
    }

    public static function createBreadcrumbsMessage(): string
    {
        return 'Янги мижоз';
    }

    public static function editBreadcrumbsMessage(): string
    {
        return 'Мижозни ўзгартириш';
    }

    public static function emptyResourceForAction(): string
    {
        return 'Бу амалларни бажариш учун малумотлар мавжуд емас';
    }

    public function onSave(ResourceRequest $request, Model $model)
    {
        $model->forceFill($request->all())->save();
        Cache::forget('customers');
        Cache::rememberForever('customers', function () {
            return \App\Models\Customer::query()->pluck('name', 'id');
        });
    }

    public function onDelete(Model $model)
    {
        if ($model->parties()->count())
        {
            Alert::error('Сотилган махсулотлар мавжудлиги учун бу мижозни ўчириш олмайсиз!');
        }elseif ($model->cards()->count() || $model->orders()->count())
        {
            Alert::error('Буюртма махсулотлар мавжудлиги учун бу мижозни ўчириш олмайсиз!');
        }elseif ($model->duties()->count())
        {
            Alert::error('Қарздорлиги мавжудлиги учун бу мижозни ўчириш олмайсиз!');
        }elseif ($model->payments()->count())
        {
            Alert::error('Тўловлари мавжудлиги учун бу мижозни ўчириш олмайсиз!');
        } else {
            $model->delete();
            Cache::forget('customers');
            Cache::rememberForever('customers', function () {
                return \App\Models\Customer::query()->pluck('name', 'id');
            });
        }
    }

}
