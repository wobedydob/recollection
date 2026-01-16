<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
