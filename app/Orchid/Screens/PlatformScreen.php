<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Expence;
use App\Orchid\Layouts\Main\ExpenceModal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'E-do\'kon - WMS';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Eletron obmorxona avtomatlashtirish tizimi';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Chiqim')
                ->icon('calculator')
                ->modal('addExpenceModal')
                ->modalTitle('Chiqim kiritish')
                ->method('addExpence')
                ->canSee(Auth::user()->hasAccess('platform.stock.expences')),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::modal('addExpenceModal', [ExpenceModal::class])
                ->applyButton('Kiritish')->closeButton('Yopish'),
        ];
    }

    public function addExpence(Request $request)
    {
        Expence::otherExpence($request->price, $request->description);
        Alert::success('Chiqim muaffaqiyatli kiritildi');
    }
}
