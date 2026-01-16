<?php

namespace App\Http\Middleware;

use App\Constants\Constants;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class CheckAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles): mixed
    {
        $user = request()->user();
        $roles = explode(',', $roles);
        if (!$user->hasRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Not allowed to access this route ');
        }
        return $next($request);
    }
}
