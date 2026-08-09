<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->user()){
            return response()->json([
                'success'=>false,
                'message'=>'Unauthenticated'
            ],401);
        }
        if($request->user()->role !== 'admin'){
            return response()->json([
                'success'=>false,
                'message'=>'Access denied. Admin only',

            ],403);
        }
        
        return $next($request);
    }
}
