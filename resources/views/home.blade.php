@extends('layouts.app')

@section('content')
<div class="home-page">
    <div class="home-header">
        <h1 class="home-title">{{ __('home.welcome', ['name' => auth()->user()->name]) }}</h1>
        <p class="home-subtitle">{{ __('home.what_to_do') }}</p>
    </div>

    <div class="module-tiles">
        @foreach(config('modules') as $key => $module)
            <a href="{{ route($module['route']) }}" class="module-tile">
                <span class="module-icon">{{ $module['icon'] }}</span>
                <h2 class="module-name">{{ __($module['name_key']) }}</h2>
                <p class="module-description">{{ __($module['description_key']) }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
