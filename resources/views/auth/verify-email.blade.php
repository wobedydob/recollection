@extends('layouts.guest')

@section('title', __('verification.title'))

@section('content')
<div class="auth-card">
    <div class="verify-icon">✉️</div>
    <h1 class="auth-title">{{ __('verification.check_email') }}</h1>
    <p class="auth-subtitle">{{ __('verification.sent_link', ['email' => auth()->user()->email]) }}</p>

    @if(session('resent'))
        <div class="success-message">
            {{ __('verification.resent') }}
        </div>
    @endif

    @if(session('error'))
        <div class="error-message">{{ session('error') }}</div>
    @endif

    <div class="verify-instructions">
        <p>{{ __('verification.instructions') }}</p>
        <ul>
            <li>{{ __('verification.check_inbox') }}</li>
            <li>{{ __('verification.check_spam') }}</li>
            <li>{{ __('verification.link_expires') }}</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('verification.resend') }}" class="auth-form">
        @csrf
        <button type="submit" class="submit-btn submit-btn--secondary">
            {{ __('verification.resend_email') }}
        </button>
    </form>

    <div class="verify-footer">
        <p class="auth-footer">
            {{ __('verification.wrong_email') }}
            <form method="POST" action="{{ route('logout') }}" class="inline-form">
                @csrf
                <button type="submit" class="auth-link-btn">{{ __('verification.logout_and_retry') }}</button>
            </form>
        </p>
    </div>
</div>
@endsection
