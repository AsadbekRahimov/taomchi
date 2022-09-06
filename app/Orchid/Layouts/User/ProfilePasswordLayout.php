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
                ->placeholder('Хозирги ишлатилаётган паролингиз')
                ->title('Хозирги паролингиз'),

            Password::make('password')
                ->placeholder('Янги киритмоқчи бўлган паролингиз')
                ->title('Янги парол'),

            Password::make('password_confirmation')
                ->placeholder('Паролни тасдиқлаш учун қайта киритинг')
                ->title('Паролни тасдиқлаш')
                ->help('Яхши парол камида камида 8 та белгидан иборат бўлиши керак, жумладан рақам ва кичик ҳарфлар қатнашиши мақсадга муоффиқ.'),
        ];
    }
}
