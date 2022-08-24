<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserEditLayout extends Rows
{
    /**
     * Views.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('user.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title('Ism')
                ->placeholder('Ism'),

            Input::make('user.email')
                ->type('email')
                ->required()
                ->title(__('Email'))
                ->placeholder(__('Email')),
            Select::make('user.branch_id')
                ->fromModel(Branch::class, 'name')
                ->title('Filial')
                ->canSee(Auth::user()->hasAccess('platform.branches'))
        ];
    }
}
