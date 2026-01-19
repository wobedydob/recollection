@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ config('modules.suggestions.name') }}</h1>
    <p class="page-subtitle">{{ config('modules.suggestions.description') }}</p>
</div>

<div id="suggestions-app"></div>
@endsection
