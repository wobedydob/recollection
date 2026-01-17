@extends('layouts.app')

@section('content')
<div class="home-page">
    <div class="home-header">
        <h1 class="home-title">Welkom, {{ auth()->user()->name }}!</h1>
        <p class="home-subtitle">Wat wil je vandaag doen?</p>
    </div>

    <div class="module-tiles">
        @foreach(config('modules') as $key => $module)
            <a href="{{ route($module['route']) }}" class="module-tile">
                <span class="module-icon">{{ $module['icon'] }}</span>
                <h2 class="module-name">{{ $module['name'] }}</h2>
                <p class="module-description">{{ $module['description'] }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
