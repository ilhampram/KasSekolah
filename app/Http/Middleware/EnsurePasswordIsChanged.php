<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if (! $request->routeIs('password.change', 'password.update.auth', 'logout')) {
                return redirect()->route('password.change')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti password awal sebelum mengakses fitur lainnya.');
            }
        }

        return $next($request);
    }
}
