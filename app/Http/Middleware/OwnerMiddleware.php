<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if ($request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Owner only.'
            ], 403);
        }

        if ($request->user()->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your owner account is not active.'
            ], 403);
        }

        return $next($request);
    }
}