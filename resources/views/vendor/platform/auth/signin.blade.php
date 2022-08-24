<div class="mb-3">

    <label class="form-label">
        Email pochta
    </label>

    {!!  \Orchid\Screen\Fields\Input::make('email')
        ->type('email')
        ->required()
        ->tabindex(1)
        ->autofocus()
        ->placeholder('Email pochtani kiriting')
    !!}
</div>

<div class="mb-3">
    <label class="form-label w-100">
        Parol
    </label>

    {!!  \Orchid\Screen\Fields\Password::make('password')
        ->required()
        ->tabindex(2)
        ->placeholder('Maxfiy parolingizni kiriting')
    !!}
</div>

<div class="row align-items-center">
    <div class="col-md-6 col-xs-12">
        <label class="form-check">
            <input type="hidden" name="remember">
            <input type="checkbox" name="remember" value="true"
                   class="form-check-input" {{ !old('remember') || old('remember') === 'true'  ? 'checked' : '' }}>
            <span class="form-check-label"> Eslab qolish</span>
        </label>
    </div>
    <div class="col-md-6 col-xs-12">
        <button id="button-login" type="submit" class="btn btn-default btn-block" tabindex="3">
            <x-orchid-icon path="login" class="small me-2"/>
            Kirish
        </button>
    </div>
</div>
