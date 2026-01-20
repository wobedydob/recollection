@extends('layouts.guest')

@section('title', __('password_reset.forgot_title'))

@section('content')
<div class="auth-card">
    <h1 class="auth-title">{{ __('password_reset.forgot_heading') }}</h1>
    <p class="auth-subtitle">{{ __('password_reset.forgot_subtitle') }}</p>

    @if(session('reset_link_sent'))
        <div class="success-message">{{ __('password_reset.link_sent') }}</div>
    @endif

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
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

        <button type="submit" class="submit-btn">
            {{ __('password_reset.send_link') }}
        </button>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}" class="auth-link">{{ __('password_reset.back_to_login') }}</a>
    </p>
</div>
@endsection
