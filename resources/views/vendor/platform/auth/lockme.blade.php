<div class="mb-3 d-flex align-items-center">
    <span class="thumb-sm avatar me-3">
        <img src="{{ $lockUser->presenter()->image() }}" class="b bg-light" alt="test">
    </span>
    <span style="width:125px;" class="small">
        <span class="text-ellipsis">{{ $lockUser->presenter()->title() }}</span>
    </span>
    <input type="hidden" name="email" required value="{{ $lockUser->email }}">
</div>

@error('email')
    <span class="d-block invalid-feedback text-danger">
            {{ $errors->first('email') }}
    </span>
@enderror

<div class="mb-3">
    <input type="hidden" name="remember" value="true">

    {!!  \Orchid\Screen\Fields\Password::make('password')
            ->required()
            ->tabindex(1)
            ->autofocus()
            ->placeholder('Maxfiy parolingizni kiriting')
    !!}
</div>

<div class="row align-items-center">
    <div class="col-md-6 col-xs-12">
        <a href="{{ route('platform.login.lock') }}" class="small">
            Boshqa profildan kirish
        </a>
    </div>
    <div class="col-md-6 col-xs-12">
        <button id="button-login" type="submit" class="btn btn-default btn-block" tabindex="2">
            <x-orchid-icon path="login" class="small me-2"/> {{__('Login')}}
        </button>
    </div>
</div>
