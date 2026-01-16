<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept','application/vnd.api+json');
        if ($locale = $request->header('locale')) {
            abort_if(!in_array($locale,['ar','en']),422,'invalid locale ');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
