<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // Het portal-cookie is een platte JWT (niet door Laravel versleuteld).
        $middleware->encryptCookies(except: [
            'wuppo_session',
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            $login = (string) config('services.wuppo.login_url', 'https://wuppo.dev/login');

            return $login.'?redirect='.rawurlencode($request->fullUrl());
        });
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\WuppoSsoAuthenticate::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        // Draai de SSO-bridge ná StartSession maar vóór de auth-check, anders
        // sorteert Laravel de auth-middleware ervoor en wordt een geldige
        // portal-gebruiker toch als gast doorgestuurd.
        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\WuppoSsoAuthenticate::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Niet ingelogd'], 401);
            }
        });
    })->create();
