<?php

namespace App\Http\Middleware;

use App\Http\Response\BaseResponse;
use Closure;
use Illuminate\Http\Request;

class HasApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // generate payload
        $payload = $request->header('Authorization')  . $request->getPathInfo() . $request->header('user-agent') . $request->header('Request-Time') . env('SECRET_KEY');
        // hash payload with sha256 to get unique api key
        $api_key = hash('sha256', $payload);
        // if there is no api key in header, reject the request
        if (!$request->header('D-App-Key')) return BaseResponse::error('Please provide api key!');
        // reject request if api key
        if ($api_key != $request->header('D-App-Key')) return BaseResponse::error('Api key does not valid!', 403);

        return $next($request);
    }
}
