<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link by email (for guests).
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always show success message to prevent email enumeration
        if (!$user) {
            return back()->with('reset_link_sent', true);
        }

        // Delete any existing tokens for this user
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Create a new token
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send the notification
        $user->notify(new ResetPasswordNotification($token));

        return back()->with('reset_link_sent', true);
    }

    /**
     * Send a password reset link to the logged-in user.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Delete any existing tokens for this user
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Create a new token
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send the notification
        $user->notify(new ResetPasswordNotification($token));

        return back()->with('reset_link_sent', true);
    }

    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('login')
                ->with('error', __('password_reset.invalid_link'));
        }

        // Verify token exists and is not expired (60 minutes)
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return redirect()->route('login')
                ->with('error', __('password_reset.invalid_link'));
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('login')
                ->with('error', __('password_reset.link_expired'));
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Verify token
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => __('password_reset.invalid_link')]);
        }

        // Check if token is expired
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => __('password_reset.link_expired')]);
        }

        // Update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('password_reset.user_not_found')]);
        }

        $user->password = $request->password;
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Log out the user if they're logged in (force re-login with new password)
        if (auth()->check()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')
            ->with('password_reset_success', true);
    }
}
