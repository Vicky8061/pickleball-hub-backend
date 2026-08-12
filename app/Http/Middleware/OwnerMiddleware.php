<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
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
                'message'=>'Unauthenticated',
            ],401);
        }
        if($request->user()->role !== 'owner'){
            return response()->json([
                'success'=>false,
                'mesaage'=>'Unauthorized. Owner Access require',
            ],403);
        }
        return $next($request);
    }
}
