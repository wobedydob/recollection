@extends('layouts.guest')

@section('title', __('password_reset.title'))

@section('content')
<div class="auth-card" data-vue-auth>
    <h1 class="auth-title">{{ __('password_reset.reset_title') }}</h1>
    <p class="auth-subtitle">{{ __('password_reset.reset_subtitle') }}</p>

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="form-group">
            <label for="password" class="label">{{ __('password_reset.new_password') }}</label>
            <password-input
                name="password"
                id="password"
                placeholder="{{ __('password_reset.new_password_placeholder') }}"
                v-model="password"
                :required="true"
            ></password-input>
            <password-strength
                :password="password"
                @validity-change="onPasswordValidityChange"
            ></password-strength>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="label">{{ __('password_reset.confirm_password') }}</label>
            <password-input
                name="password_confirmation"
                id="password_confirmation"
                placeholder="{{ __('password_reset.confirm_password_placeholder') }}"
                v-model="passwordConfirmation"
                :required="true"
                :show-match="passwordsMatch"
                :input-class="passwordsMatch === true ? 'match' : (passwordsMatch === false ? 'no-match' : '')"
            ></password-input>
            <p v-if="passwordsMatch === false" class="match-message no-match">{{ __('profile.passwords_no_match') }}</p>
            <p v-else-if="passwordsMatch === true" class="match-message match">{{ __('profile.passwords_match') }}</p>
        </div>

        <button type="submit" class="submit-btn" :disabled="!passwordValid || passwordsMatch === false">
            {{ __('password_reset.reset_button') }}
        </button>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}" class="auth-link">{{ __('password_reset.back_to_login') }}</a>
    </p>
</div>
@endsection
