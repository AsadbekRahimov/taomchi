@extends('platform::dashboard')

@section('title', '404')
@section('description', 'Siz izlagan saxifa mavjud emas!')

@section('content')

    <div class="container p-md-5 layout">
        <div class="display-1 text-muted mb-5 mt-sm-5 mt-0">
            <x-orchid-icon path="bug"/>
            404
        </div>
        <h1 class="h2 mb-3">Siz izlagan saxifa mavjud emas!</h1>
    </div>

@endsection
