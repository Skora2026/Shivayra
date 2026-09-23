<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Guests land on the login that matches where they were going:
        // storefront routes -> /login, admin routes -> /admin/login.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.login')
            : route('login'));

        // Authenticated users browsing guest-only pages (login/register) go to
        // their customer dashboard.
        RedirectIfAuthenticated::redirectUsing(fn () => route('my-account'));

        // Payment gateways call webhooks server-to-server: no CSRF token, authenticity via signature
        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Validation errors / auth failures must come back as JSON whenever the
        // client asked for JSON (Accept: application/json, X-Requested-With) —
        // not only on api/* routes. The register page's OTP fetch posts JSON to
        // web routes; without this its 422s were rendered as HTML redirects.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson() || $request->is('api/*'),
        );
    })->create();
