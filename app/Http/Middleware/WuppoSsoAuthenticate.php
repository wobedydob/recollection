<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\WuppoSso;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Brug tussen de wuppo.dev-portal en de Laravel-auth.
 *
 * - Geldig portal-token → bijbehorende gebruiker (op e-mail) opzoeken of
 *   aanmaken en inloggen. Zo ben je automatisch ingelogd zodra je bij de
 *   portal bent ingelogd.
 * - Geen/ongeldig token → alleen SSO-beheerde sessies weer uitloggen, zodat
 *   een eigen (native) login onaangetast blijft. De redirect naar de
 *   portal-login regelt de standaard 'auth'-middleware (redirectGuestsTo).
 *
 * Draait in de web-groep ná StartSession, dus de sessie is beschikbaar.
 */
class WuppoSsoAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret  = (string) config('services.wuppo.sso_secret', '');
        $jwksUrl = (string) config('services.wuppo.jwks_url', 'https://hello.wuppo.dev/.well-known/jwks.json');
        $claims  = WuppoSso::verify((string) $request->cookie('wuppo_session'), $secret, $jwksUrl);

        if ($claims !== null) {
            $email = strtolower(trim((string) ($claims['email'] ?? '')));
            if ($email !== '') {
                $current = Auth::user();
                if (! $current || strtolower((string) $current->email) !== $email) {
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => ($claims['name'] ?? '') !== '' ? $claims['name'] : $email,
                            'password' => Hash::make(Str::random(48)),
                            'email_verified_at' => now(),
                        ],
                    );
                    // Portal vouwt voor de identiteit → altijd geverifieerd.
                    if ($user->email_verified_at === null) {
                        $user->forceFill(['email_verified_at' => now()])->save();
                    }
                    Auth::login($user);
                    $request->session()->put('wuppo_sso_managed', true);
                }
            }
        } elseif ($request->session()->get('wuppo_sso_managed') && Auth::check()) {
            // Portal-sessie is weg → deze automatisch aangemaakte sessie ook.
            Auth::logout();
            $request->session()->forget('wuppo_sso_managed');
        }

        return $next($request);
    }
}
