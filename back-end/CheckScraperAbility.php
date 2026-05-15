<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckScraperAbility
{
    //Checks if user has scraper abilities
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->tokenCan('scraper:run')) {
            abort(403, 'Unauthorized. This endpoint is for the scraper bot only.');
        }

        return $next($request);
    }
}