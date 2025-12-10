<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isOwner()) {
            return response()->json(['message' => 'Unauthorized - Owners only'], 403);
        }
        return $next($request);
    }
}