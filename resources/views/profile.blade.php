@extends('layouts.app')

@section('content')
<div class="profile-page">
    <div class="profile-card" data-vue-auth>
        <div class="profile-header">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="profile-info">
                <h1 class="profile-name">{{ auth()->user()->name }}</h1>
                <p class="profile-email">{{ auth()->user()->email }}</p>
                <p class="profile-member">{{ __('profile.member_since', ['date' => auth()->user()->created_at->translatedFormat('F Y')]) }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
            @csrf
            @method('PUT')
            <h2 class="section-title">{{ __('profile.edit') }}</h2>

            @if(session('profile_success'))
                <div class="success-message">{{ session('profile_success') }}</div>
            @endif

            <div class="form-group">
                <label for="name" class="label">{{ __('profile.display_name') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input"
                    value="{{ auth()->user()->name }}"
                    placeholder="{{ __('profile.your_name') }}"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email" class="label">{{ __('profile.email') }}</label>
                <input
                    type="email"
                    id="email"
                    value="{{ auth()->user()->email }}"
                    class="input"
                    disabled
                />
                <p class="input-hint">{{ __('profile.email_hint') }}</p>
            </div>

            <button type="submit" class="save-btn">
                {{ __('profile.save_changes') }}
            </button>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="password-form">
            @csrf
            @method('PUT')
            <h2 class="section-title">{{ __('profile.change_password') }}</h2>

            @if(session('password_success'))
                <div class="success-message">{{ session('password_success') }}</div>
            @endif

            @if($errors->any())
                <div class="error-message">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label for="current_password" class="label">{{ __('profile.current_password') }}</label>
                <password-input
                    name="current_password"
                    id="current_password"
                    placeholder="{{ __('profile.current_password_placeholder') }}"
                    v-model="currentPassword"
                    :required="true"
                ></password-input>
            </div>

            <div class="form-group">
                <label for="new_password" class="label">{{ __('profile.new_password') }}</label>
                <password-input
                    name="password"
                    id="new_password"
                    placeholder="{{ __('profile.new_password_placeholder') }}"
                    v-model="newPassword"
                    :required="true"
                ></password-input>
                <password-strength
                    :password="newPassword"
                    @validity-change="onPasswordValidityChange"
                ></password-strength>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation" class="label">{{ __('profile.confirm_new_password') }}</label>
                <password-input
                    name="password_confirmation"
                    id="new_password_confirmation"
                    placeholder="{{ __('profile.confirm_password_placeholder') }}"
                    v-model="newPasswordConfirmation"
                    :required="true"
                    :show-match="newPasswordsMatch"
                    :input-class="newPasswordsMatch === true ? 'match' : (newPasswordsMatch === false ? 'no-match' : '')"
                ></password-input>
                <p v-if="newPasswordsMatch === false" class="match-message no-match">{{ __('profile.passwords_no_match') }}</p>
                <p v-else-if="newPasswordsMatch === true" class="match-message match">{{ __('profile.passwords_match') }}</p>
            </div>

            <button type="submit" class="save-btn" :disabled="!passwordValid || newPasswordsMatch === false">
                {{ __('profile.change_password') }}
            </button>
        </form>

        <div class="settings-section">
            <h2 class="section-title">{{ __('profile.settings') }}</h2>

            <div class="setting-item">
                <span class="setting-label">{{ __('profile.theme') }}</span>
                <div class="theme-switcher">
                    <button
                        type="button"
                        class="theme-option {{ auth()->user()->theme !== 'dark' ? 'active' : '' }}"
                        onclick="setTheme('light')"
                    >
                        ☀️ {{ __('profile.light') }}
                    </button>
                    <button
                        type="button"
                        class="theme-option {{ auth()->user()->theme === 'dark' ? 'active' : '' }}"
                        onclick="setTheme('dark')"
                    >
                        🌙 {{ __('profile.dark') }}
                    </button>
                </div>
            </div>

            <div class="setting-item">
                <span class="setting-label">{{ __('profile.color') }}</span>
                <div class="color-theme-switcher">
                    <button type="button" class="color-theme-option pink {{ auth()->user()->color_theme === 'pink' || !auth()->user()->color_theme ? 'active' : '' }}" onclick="setColorTheme('pink')">
                    </button>
                    <button type="button" class="color-theme-option blue {{ auth()->user()->color_theme === 'blue' ? 'active' : '' }}" onclick="setColorTheme('blue')">
                    </button>
                    <button type="button" class="color-theme-option green {{ auth()->user()->color_theme === 'green' ? 'active' : '' }}" onclick="setColorTheme('green')">
                    </button>
                    <button type="button" class="color-theme-option orange {{ auth()->user()->color_theme === 'orange' ? 'active' : '' }}" onclick="setColorTheme('orange')">
                    </button>
                </div>
            </div>

            <div class="setting-item">
                <span class="setting-label">{{ __('profile.language') }}</span>
                <div class="language-switcher">
                    <button
                        type="button"
                        class="language-option {{ auth()->user()->locale === 'nl' || !auth()->user()->locale ? 'active' : '' }}"
                        onclick="setLocale('nl')"
                    >
                        NL
                    </button>
                    <button
                        type="button"
                        class="language-option {{ auth()->user()->locale === 'en' ? 'active' : '' }}"
                        onclick="setLocale('en')"
                    >
                        EN
                    </button>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <div class="profile-nav-links">
                <span class="profile-nav-label">{{ __('profile.back_to') }}</span>
                <a href="{{ route('home') }}" class="profile-nav-btn" title="Home">🏠</a>
                <a href="{{ route('memorybox.index') }}" class="profile-nav-btn" title="{{ config('modules.memorybox.name') }}">{{ config('modules.memorybox.icon') }}</a>
                <a href="{{ route('checklist.index') }}" class="profile-nav-btn" title="{{ config('modules.checklist.name') }}">{{ config('modules.checklist.icon') }}</a>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">{{ __('common.logout') }}</button>
            </form>
        </div>

        <div class="danger-zone">
            <h2 class="section-title danger-title">{{ __('profile.danger_zone') }}</h2>
            <p class="danger-description">{{ __('profile.delete_warning') }}</p>
            <button type="button" class="delete-account-btn" onclick="confirmDeleteAccount()">{{ __('profile.delete_account') }}</button>
        </div>
    </div>
</div>

<!-- Language Change Loader -->
<div class="page-loader-overlay" id="locale-loader" style="display: none;">
    <div class="page-loader-content">
        <div class="loader"></div>
        <p class="loader-text">{{ __('common.loading') }}</p>
    </div>
</div>

<div class="modal-overlay" id="delete-account-modal" style="display: none;">
    <div class="modal">
        <button type="button" class="close-btn" onclick="closeDeleteModal()">×</button>
        <h2 class="modal-title">{{ __('profile.delete_confirm_title') }}</h2>
        <p class="modal-text">{{ __('profile.delete_confirm_text') }}</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">{{ __('common.cancel') }}</button>
            <form method="POST" action="{{ route('profile.delete') }}" style="flex: 1;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-full">{{ __('profile.delete_confirm_btn') }}</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDeleteAccount() {
    document.getElementById('delete-account-modal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('delete-account-modal').style.display = 'none';
}
</script>
@endpush
@endsection
