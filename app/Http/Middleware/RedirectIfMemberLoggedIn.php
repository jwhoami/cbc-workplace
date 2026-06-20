<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pipeline\Pipeline;

class RedirectIfMemberLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = config('session.cookie');

        if ($request->hasCookie($cookieName)) {
            return (new Pipeline(app()))
                ->send($request)
                ->through([
                    \App\Http\Middleware\EncryptCookies::class,
                    \Illuminate\Session\Middleware\StartSession::class,
                ])
                ->then(function ($request) use ($next) {
                    if (auth('member')->check()) {
                        return redirect('/member');
                    }
                    return $next($request);
                });
        }

        return $next($request);
    }
}
