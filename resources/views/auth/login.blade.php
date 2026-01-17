@extends('layouts.guest')

@section('title', 'Inloggen - Recollectie')

@section('content')
<div class="auth-card" data-vue-auth>
    <h1 class="auth-title">Welkom!</h1>
    <p class="auth-subtitle">Log in bij je Recollectie</p>

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="email" class="label">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                class="input"
                placeholder="jij@voorbeeld.nl"
                value="{{ old('email') }}"
                required
                autofocus
            />
        </div>

        <div class="form-group">
            <label for="password" class="label">Wachtwoord</label>
            <password-input
                name="password"
                id="password"
                placeholder="Je wachtwoord"
                v-model="password"
                :required="true"
            ></password-input>
        </div>

        <button type="submit" class="submit-btn">
            Inloggen
        </button>
    </form>

    <p class="auth-footer">
        Nog geen account?
        <a href="{{ route('register') }}" class="auth-link">Maak er een aan</a>
    </p>
</div>
@endsection
