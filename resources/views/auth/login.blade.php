@extends('layouts.guest')

@section('title', __('auth.login_title'))

@section('content')
<div class="auth-card" data-vue-auth>
    <h1 class="auth-title">{{ __('auth.welcome') }}</h1>
    <p class="auth-subtitle">{{ __('auth.login_subtitle') }}</p>

    @if(session('password_reset_success'))
        <div class="success-message">{{ __('password_reset.success') }}</div>
    @endif

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
            <label for="email" class="label">{{ __('auth.email') }}</label>
            <input
                type="email"
                id="email"
                name="email"
                class="input"
                placeholder="{{ __('auth.your_email') }}"
                value="{{ old('email') }}"
                required
                autofocus
            />
        </div>

        <div class="form-group">
            <label for="password" class="label">
                {{ __('auth.password') }}
                <a href="{{ route('password.request') }}" class="forgot-link">{{ __('auth.forgot_password') }}</a>
            </label>
            <password-input
                name="password"
                id="password"
                placeholder="{{ __('auth.password') }}"
                v-model="password"
                :required="true"
            ></password-input>
        </div>

        <button type="submit" class="submit-btn">
            {{ __('auth.login') }}
        </button>
    </form>

    <p class="auth-footer">
        {{ __('auth.no_account') }}
        <a href="{{ route('register') }}" class="auth-link">{{ __('auth.create_one') }}</a>
    </p>
</div>
@endsection
