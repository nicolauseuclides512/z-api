<?php

namespace App\Http\Middleware;

use App\Cores\Jsonable;
use Closure;

class ValidateServiceToken
{
    use Jsonable;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //define header X-localization
        $serviceID = $request->hasHeader('X-Service-ID')
            ? $request->header('X-Service-ID')
            : null;

        //make a static token to simplify connection between service
        if ($serviceID == 'ng5q4z9QSQtp9KZC2gicmUWcVLqwLyGP')
            // continue request
            return $next($request);

        return $this->jsonErrors(['error' => 'Unauthenticated.'], 401);
    }
}
