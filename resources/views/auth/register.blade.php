@extends('layouts.guest')

@section('title', __('auth.register_title'))

@section('content')
<div class="auth-card" data-vue-auth>
    <h1 class="auth-title">{{ __('auth.create_account') }}</h1>
    <p class="auth-subtitle">{{ __('auth.register_subtitle') }}</p>

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="form-group">
            <label for="name" class="label">{{ __('auth.name') }}</label>
            <input
                type="text"
                id="name"
                name="name"
                class="input"
                placeholder="{{ __('auth.your_name') }}"
                value="{{ old('name') }}"
                required
                autofocus
            />
        </div>

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
            />
        </div>

        <div class="form-group">
            <label for="password" class="label">{{ __('auth.password') }}</label>
            <password-input
                name="password"
                id="password"
                placeholder="{{ __('auth.create_password') }}"
                v-model="password"
                :required="true"
            ></password-input>
            <password-strength
                :password="password"
                @validity-change="onPasswordValidityChange"
            ></password-strength>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="label">{{ __('auth.confirm_password') }}</label>
            <password-input
                name="password_confirmation"
                id="password_confirmation"
                placeholder="{{ __('auth.repeat_password') }}"
                v-model="passwordConfirmation"
                :required="true"
                :show-match="passwordsMatch"
                :input-class="passwordsMatch === true ? 'match' : (passwordsMatch === false ? 'no-match' : '')"
            ></password-input>
            <p v-if="passwordsMatch === false" class="match-message no-match">{{ __('profile.passwords_no_match') }}</p>
            <p v-else-if="passwordsMatch === true" class="match-message match">{{ __('profile.passwords_match') }}</p>
        </div>

        <div class="form-group">
            <label class="label">{{ __('auth.preferred_language') }}</label>
            <div class="language-switcher">
                <button
                    type="button"
                    class="language-option {{ old('locale', 'nl') === 'nl' ? 'active' : '' }}"
                    onclick="selectLocale('nl')"
                >
                    NL
                </button>
                <button
                    type="button"
                    class="language-option {{ old('locale') === 'en' ? 'active' : '' }}"
                    onclick="selectLocale('en')"
                >
                    EN
                </button>
            </div>
            <input type="hidden" name="locale" id="locale-input" value="{{ old('locale', 'nl') }}" />
        </div>

        <button type="submit" class="submit-btn" :disabled="!passwordValid || passwordsMatch === false">
            {{ __('auth.register') }}
        </button>
    </form>

    <p class="auth-footer">
        {{ __('auth.have_account') }}
        <a href="{{ route('login') }}" class="auth-link">{{ __('auth.login') }}</a>
    </p>
</div>

@push('scripts')
<script>
function selectLocale(locale) {
    document.getElementById('locale-input').value = locale;
    document.querySelectorAll('.language-option').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.trim() === locale.toUpperCase());
    });
}
</script>
@endpush
@endsection
