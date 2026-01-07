<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        // $middleware->append(EnsureOnboardingCompleted::class);
        $middleware->alias([
            'onboarded' => EnsureOnboardingCompleted::class,
            "is_admin" => EnsureUserIsAdmin::class,
        ]);
        // $middleware->redirectGuestsTo('/login');
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                // $model = class_basename($e->getModel());
                return response()->json([
                    "message" => "Resource not found"
                ], 404);
            }
        });
    })->create();
