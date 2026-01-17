@extends('layouts.app')

@section('content')
<div class="profile-page">
    <div class="profile-card" data-vue-auth>
        <div class="profile-header">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="profile-info">
                <h1 class="profile-name">{{ auth()->user()->name }}</h1>
                <p class="profile-email">{{ auth()->user()->email }}</p>
                <p class="profile-member">Lid sinds {{ auth()->user()->created_at->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
            @csrf
            @method('PUT')
            <h2 class="section-title">Profiel Bewerken</h2>

            @if(session('profile_success'))
                <div class="success-message">{{ session('profile_success') }}</div>
            @endif

            <div class="form-group">
                <label for="name" class="label">Weergavenaam</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="input"
                    value="{{ auth()->user()->name }}"
                    placeholder="Je naam"
                    required
                />
            </div>

            <div class="form-group">
                <label for="email" class="label">E-mail</label>
                <input
                    type="email"
                    id="email"
                    value="{{ auth()->user()->email }}"
                    class="input"
                    disabled
                />
                <p class="input-hint">E-mail kan niet worden gewijzigd</p>
            </div>

            <button type="submit" class="save-btn">
                Wijzigingen Opslaan
            </button>
        </form>

        <form method="POST" action="{{ route('profile.password') }}" class="password-form">
            @csrf
            @method('PUT')
            <h2 class="section-title">Wachtwoord Wijzigen</h2>

            @if(session('password_success'))
                <div class="success-message">{{ session('password_success') }}</div>
            @endif

            @if($errors->any())
                <div class="error-message">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label for="current_password" class="label">Huidig wachtwoord</label>
                <password-input
                    name="current_password"
                    id="current_password"
                    placeholder="Je huidige wachtwoord"
                    v-model="currentPassword"
                    :required="true"
                ></password-input>
            </div>

            <div class="form-group">
                <label for="new_password" class="label">Nieuw wachtwoord</label>
                <password-input
                    name="password"
                    id="new_password"
                    placeholder="Maak een sterk wachtwoord"
                    v-model="newPassword"
                    :required="true"
                ></password-input>
                <password-strength
                    :password="newPassword"
                    @validity-change="onPasswordValidityChange"
                ></password-strength>
            </div>

            <div class="form-group">
                <label for="new_password_confirmation" class="label">Bevestig nieuw wachtwoord</label>
                <password-input
                    name="password_confirmation"
                    id="new_password_confirmation"
                    placeholder="Herhaal nieuw wachtwoord"
                    v-model="newPasswordConfirmation"
                    :required="true"
                    :show-match="newPasswordsMatch"
                    :input-class="newPasswordsMatch === true ? 'match' : (newPasswordsMatch === false ? 'no-match' : '')"
                ></password-input>
                <p v-if="newPasswordsMatch === false" class="match-message no-match">Wachtwoorden komen niet overeen</p>
                <p v-else-if="newPasswordsMatch === true" class="match-message match">Wachtwoorden komen overeen</p>
            </div>

            <button type="submit" class="save-btn" :disabled="!passwordValid || newPasswordsMatch === false">
                Wachtwoord Wijzigen
            </button>
        </form>

        <div class="settings-section">
            <h2 class="section-title">Instellingen</h2>

            <div class="setting-item">
                <span class="setting-label">Thema</span>
                <div class="theme-switcher">
                    <button
                        type="button"
                        class="theme-option {{ auth()->user()->theme !== 'dark' ? 'active' : '' }}"
                        onclick="setTheme('light')"
                    >
                        ☀️ Licht
                    </button>
                    <button
                        type="button"
                        class="theme-option {{ auth()->user()->theme === 'dark' ? 'active' : '' }}"
                        onclick="setTheme('dark')"
                    >
                        🌙 Donker
                    </button>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <div class="profile-nav-links">
                <span class="profile-nav-label">Terug naar:</span>
                <a href="{{ route('home') }}" class="profile-nav-btn" title="Home">🏠</a>
                <a href="{{ route('memorybox.index') }}" class="profile-nav-btn" title="Memory Box">✨</a>
                <a href="{{ route('checklist.index') }}" class="profile-nav-btn" title="Checklist">📋</a>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Uitloggen</button>
            </form>
        </div>
    </div>
</div>
@endsection
