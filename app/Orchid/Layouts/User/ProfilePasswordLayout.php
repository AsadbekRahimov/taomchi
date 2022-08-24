<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Layouts\Rows;

class ProfilePasswordLayout extends Rows
{
    /**
     * Views.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Password::make('old_password')
                ->placeholder(__('Xozirgi ishlatilayotgan parolingiz'))
                ->title(__('Xozirgi parolingiz'))
                ->help('This is your password set at the moment.'),

            Password::make('password')
                ->placeholder(__('Yangi kiritmoqchi bo`lgan parolingiz'))
                ->title('Yangi parol'),

            Password::make('password_confirmation')
                ->placeholder('Parolni tasdiqlash uchun qayta kiriting')
                ->title('Parolni tasdiqlash')
                ->help('Yaxshi parol kamida kamida 8 ta belgidan iborat bo`lishi kerak, jumladan raqam va kichik harflar qatnashishi maqsadga muoffiq.'),
        ];
    }
}
