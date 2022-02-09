<?php

namespace App\Http\Middleware;

use App\Cores\Jsonable;
use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthTokenMiddleware
{
    use Jsonable;

    protected $authToken;

    public function __construct(AuthToken $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @internal param AuthToken $authToken
     */
    public function handle(Request $request, Closure $next)
    {
        try {

            if ($request->wantsJson() &&
                $this->authToken->verify($request->bearerToken())) {
                return $next($request);
            }

            return $this->json(
                Response::HTTP_BAD_REQUEST,
                'Unauthorized.');

        } catch (\Exception $e) {
            return $this->jsonExceptions($e);
        }
    }
}
