@extends('platform::auth')
@section('title', 'Shaxsiy profilga kirish')

@section('content')
    <h1 class="h4 text-black mb-4">Shaxsiy profilga kirish</h1>

    <form class="m-t-md"
          role="form"
          method="POST"
          data-controller="form"
          data-action="form#submit"
          data-form-button-animate="#button-login"
          data-form-button-text="Kirish.."
          action="{{ route('platform.login.auth') }}">
        @csrf

        @includeWhen($isLockUser,'platform::auth.lockme')
        @includeWhen(!$isLockUser,'platform::auth.signin')
    </form>
@endsection
