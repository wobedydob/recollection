@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ config('modules.checklist.name') }}</h1>
    <p class="page-subtitle">{{ config('modules.checklist.description') }}</p>
</div>

<div id="todo-app"></div>
@endsection
