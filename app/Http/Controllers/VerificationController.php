<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request): RedirectResponse|View
    {
        // If already verified, redirect to home
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request): RedirectResponse
    {
        // Check if user ID matches
        if ($request->route('id') != $request->user()->getKey()) {
            return redirect()->route('verification.notice')
                ->with('error', __('verification.invalid_link'));
        }

        // Check if hash matches
        if (! hash_equals(sha1($request->user()->getEmailForVerification()), (string) $request->route('hash'))) {
            return redirect()->route('verification.notice')
                ->with('error', __('verification.invalid_link'));
        }

        // If already verified
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')
                ->with('verified', true);
        }

        // Mark as verified
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('home')
            ->with('verified', true);
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', true);
    }
}
