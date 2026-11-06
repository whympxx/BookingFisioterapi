<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
        // Di Vercel, aplikasi berada di balik proxy HTTPS; percayai header-nya agar URL tetap https.
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn ($request) => route($request->user()?->homeRoute() ?? 'dashboard'));
        // Notifikasi dari Midtrans dikirim server-ke-server, tanpa token CSRF.
        $middleware->validateCsrfTokens(except: ['pembayaran/notifikasi']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log Vercel memotong pesan panjang; tulis ringkasan satu baris agar penyebab error terbaca.
        $exceptions->report(function (Throwable $e) {
            if (! env('VERCEL')) {
                return; // lokal: log Laravel biasa (lengkap dengan stack trace)
            }
            error_log('[app-error] '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());

            return false; // jangan tulis stack trace panjang yang membuat pesan terpotong
        });
    })->create();
