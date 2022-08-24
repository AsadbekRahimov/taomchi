<?php

declare(strict_types=1);

namespace App\Orchid\Screens\User;

use App\Orchid\Layouts\Role\RolePermissionLayout;
use App\Orchid\Layouts\User\UserEditLayout;
use App\Orchid\Layouts\User\UserPasswordLayout;
use App\Orchid\Layouts\User\UserRoleLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Orchid\Access\UserSwitch;
use App\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserEditScreen extends Screen
{
    /**
     * @var User
     */
    public $user;

    /**
     * Query data.
     *
     * @param User $user
     *
     * @return array
     */
    public function query(User $user): iterable
    {
        $user->load(['roles']);

        return [
            'user'       => $user,
            'permission' => $user->getStatusPermission(),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->user->exists ? 'Foydalanuvchini o`zgartirish' : 'Yangi foydalanuvchi';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Foydalanuvchining shaxsiy profil malumotlari';
    }

    /**
     * @return iterable|null
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.users',
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Foydalanuvchi rolidan foydalanish'))
                ->icon('login')
                ->confirm('Siz o`z profilingizga tizimda chiqish tugmasini bosib qaytishingiz mumkin')
                ->method('loginAs')
                ->canSee($this->user->exists && \request()->user()->id !== $this->user->id),

            Button::make('Foydalanuvchini o`chirish')
                ->icon('trash')
                ->confirm('Siz rostdan ham ushbu foydalanuvchini o`chiqmoqchimisiz')
                ->method('remove')
                ->canSee($this->user->exists),

            Button::make('Foydalanuvchini saqlash')
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [

            Layout::block(UserEditLayout::class)
                ->title('Profil haqida ma`lumot')
                ->description('Shaxsiy profil ma\'lumotlari va elektron pochta manzilini yangilash.')
                ->commands(
                    Button::make('Saqlash')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserPasswordLayout::class)
                ->title('Parolni yangilash')
                ->description('Xavfsizlikni saqlash uchun parolingizni uzun, tasodifiy belgilardan foydalanayotganligingizga ishonch hosil qiling.')
                ->commands(
                    Button::make('Saqlash')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(UserRoleLayout::class)
                ->title('Rollar')
                ->description('Rollar foydalanuvchilarga ayrim ammalarnibajarish uchun huquq beradi.')
                ->commands(
                    Button::make('Saqlash')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

            Layout::block(RolePermissionLayout::class)
                ->title('Huqular')
                ->description('Huqular foydalanuvchilarga aynan bir amalni bajarish uchun kerak boladi.')
                ->commands(
                    Button::make('Saqlash')
                        ->type(Color::DEFAULT())
                        ->icon('check')
                        ->canSee($this->user->exists)
                        ->method('save')
                ),

        ];
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(User $user, Request $request)
    {
        //dd($request->collect('user'));
        $request->validate([
            'user.email' => [
                'required',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
        ]);

        $permissions = collect($request->get('permissions'))
            ->map(function ($value, $key) {
                return [base64_decode($key) => $value];
            })
            ->collapse()
            ->toArray();

        $user->when($request->filled('user.password'), function (Builder $builder) use ($request) {
            $builder->getModel()->password = Hash::make($request->input('user.password'));
        });

        $user
            ->fill($request->collect('user')->except(['password', 'permissions', 'roles'])->toArray())
            ->fill(['permissions' => $permissions])
            //->fill(['branch_id' => $request->user['branch_id']])
            ->save();

        $user->replaceRoles($request->input('user.roles'));

        Toast::info('Foydalanuvchi malumotlari saqlandi');

        return redirect()->route('platform.systems.users');
    }

    /**
     * @param User $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     */
    public function remove(User $user)
    {
        $user->delete();

        Toast::info('Foydalanuvchi o`chirildi');

        return redirect()->route('platform.systems.users');
    }

    /**
     * @param User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginAs(User $user)
    {
        UserSwitch::loginAs($user);

        Toast::info('Siz rostdan ham ushbu foydalanuvchidan foydalanmoqchimsiz?');

        return redirect()->route(config('platform.index'));
    }
}
