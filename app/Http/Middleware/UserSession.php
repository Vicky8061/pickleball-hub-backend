<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSession
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        if (!session('logged_in')) {

            return redirect('/login');

        }

        if (session('auth_user.role') !== 'user') {

            return redirect('/login');

        }

        return $next($request);
    }
}