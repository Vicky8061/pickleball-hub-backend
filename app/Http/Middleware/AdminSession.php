<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSession
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        if (!session('logged_in')) {
            return redirect('/login');
        }

        $role = session('auth_user.role');

        if ($role === 'user') {
            return redirect('/user/dashboard');
        }

        if ($role === 'owner') {
            return redirect('/owner/dashboard');
        }

        if ($role !== 'admin') {
            return redirect('/login');
        }

        /** @var Response $response */
        $response = $next($request);

        return $response->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
