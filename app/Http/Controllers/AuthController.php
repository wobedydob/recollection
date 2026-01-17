<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Web Views
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function showProfile(): View
    {
        return view('profile');
    }

    // Web Form Handlers
    public function webRegister(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Naam is verplicht',
            'email.required' => 'E-mailadres is verplicht',
            'email.email' => 'Ongeldig e-mailadres',
            'email.unique' => 'E-mailadres is al in gebruik',
            'password.required' => 'Wachtwoord is verplicht',
            'password.min' => 'Wachtwoord moet minimaal 6 tekens zijn',
            'password.confirmed' => 'Wachtwoorden komen niet overeen',
        ]);

        $user = User::create([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'name' => $request->input('name'),
        ]);

        Auth::login($user);

        return redirect()->route('ideas.index');
    }

    public function webLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'E-mailadres is verplicht',
            'email.email' => 'Ongeldig e-mailadres',
            'password.required' => 'Wachtwoord is verplicht',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Ongeldige inloggegevens'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended(route('ideas.index'));
    }

    public function webLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function webUpdateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Naam is verplicht',
        ]);

        $user = $request->user();
        $user->name = trim($request->input('name'));
        $user->save();

        return back()->with('profile_success', 'Profiel succesvol bijgewerkt!');
    }

    public function webUpdatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Huidig wachtwoord is verplicht',
            'password.required' => 'Nieuw wachtwoord is verplicht',
            'password.min' => 'Nieuw wachtwoord moet minimaal 6 tekens zijn',
            'password.confirmed' => 'Wachtwoorden komen niet overeen',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Huidig wachtwoord is onjuist']);
        }

        $user->password = $request->input('password');
        $user->save();

        return back()->with('password_success', 'Wachtwoord succesvol gewijzigd!');
    }

    // API Methods (kept for Vue components)
    public function register(Request $request): JsonResponse
    {
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');
        $name = trim($request->input('name', ''));

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Ongeldig e-mailadres'], 400);
        }

        if (strlen($password) < 6) {
            return response()->json(['message' => 'Wachtwoord moet minimaal 6 tekens zijn'], 400);
        }

        if (!$name) {
            return response()->json(['message' => 'Naam is verplicht'], 400);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json(['message' => 'E-mailadres is al in gebruik'], 400);
        }

        $user = User::create([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ]);

        Auth::login($user);

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');

        if (!$email || !$password) {
            return response()->json(['message' => 'E-mail en wachtwoord zijn verplicht'], 400);
        }

        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json(['message' => 'Ongeldige inloggegevens'], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->formatUser(Auth::user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user ? $this->formatUser($user) : null,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $name = $request->input('name');
        $avatar = $request->input('avatar');

        if ($name === null && $avatar === null) {
            return response()->json(['message' => 'Niets om bij te werken'], 400);
        }

        if ($name !== null) {
            $user->name = trim($name);
        }

        if ($avatar !== null) {
            $user->avatar = $avatar;
        }

        $user->save();

        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $currentPassword = $request->input('currentPassword', '');
        $newPassword = $request->input('newPassword', '');

        if (!$currentPassword || !$newPassword) {
            return response()->json(['message' => 'Huidig en nieuw wachtwoord zijn verplicht'], 400);
        }

        if (strlen($newPassword) < 6) {
            return response()->json(['message' => 'Nieuw wachtwoord moet minimaal 6 tekens zijn'], 400);
        }

        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json(['message' => 'Huidig wachtwoord is onjuist'], 400);
        }

        $user->password = $newPassword;
        $user->save();

        return response()->json(['success' => true]);
    }

    public function updateTheme(Request $request): JsonResponse
    {
        $theme = $request->input('theme');

        if (!in_array($theme, ['light', 'dark'])) {
            return response()->json(['message' => 'Ongeldig thema'], 400);
        }

        $request->user()->update(['theme' => $theme]);

        return response()->json(['theme' => $theme]);
    }

    public function updateColorTheme(Request $request): JsonResponse
    {
        $colorTheme = $request->input('color_theme');

        if (!in_array($colorTheme, ['pink', 'blue', 'green', 'orange'])) {
            return response()->json(['message' => 'Ongeldig kleurthema'], 400);
        }

        $request->user()->update(['color_theme' => $colorTheme]);

        return response()->json(['color_theme' => $colorTheme]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'createdAt' => $user->created_at->getTimestampMs(),
        ];
    }
}
