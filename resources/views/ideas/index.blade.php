@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ config('modules.memorybox.name') }}</h1>
    <p class="page-subtitle">{{ config('modules.memorybox.description') }}</p>
</div>

<div id="ideas-app"></div>
@endsection
