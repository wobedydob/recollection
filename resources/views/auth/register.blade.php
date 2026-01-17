@extends('layouts.guest')

@section('title', 'Registreren - Recollectie')

@section('content')
<div class="auth-card" data-vue-auth>
    <h1 class="auth-title">Account Aanmaken</h1>
    <p class="auth-subtitle">Begin je Recollectie avontuur</p>

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="name" class="label">Naam</label>
            <input
                type="text"
                id="name"
                name="name"
                class="input"
                placeholder="Je naam"
                value="{{ old('name') }}"
                required
                autofocus
            />
        </div>

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
            />
        </div>

        <div class="form-group">
            <label for="password" class="label">Wachtwoord</label>
            <password-input
                name="password"
                id="password"
                placeholder="Maak een sterk wachtwoord"
                v-model="password"
                :required="true"
            ></password-input>
            <password-strength
                :password="password"
                @validity-change="onPasswordValidityChange"
            ></password-strength>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="label">Wachtwoord bevestigen</label>
            <password-input
                name="password_confirmation"
                id="password_confirmation"
                placeholder="Herhaal je wachtwoord"
                v-model="passwordConfirmation"
                :required="true"
                :show-match="passwordsMatch"
                :input-class="passwordsMatch === true ? 'match' : (passwordsMatch === false ? 'no-match' : '')"
            ></password-input>
            <p v-if="passwordsMatch === false" class="match-message no-match">Wachtwoorden komen niet overeen</p>
            <p v-else-if="passwordsMatch === true" class="match-message match">Wachtwoorden komen overeen</p>
        </div>

        <button type="submit" class="submit-btn" :disabled="!passwordValid || passwordsMatch === false">
            Account Aanmaken
        </button>
    </form>

    <p class="auth-footer">
        Heb je al een account?
        <a href="{{ route('login') }}" class="auth-link">Inloggen</a>
    </p>
</div>
@endsection
