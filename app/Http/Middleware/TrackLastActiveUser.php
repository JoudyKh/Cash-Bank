<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class TrackLastActiveUser
{

    public function handle(Request $request, Closure $next)
    {

        if (request()->user()) {
            $request->user()->update(['last_active_at' => Carbon::now()]);
        }


        return $next($request);
    }
}
