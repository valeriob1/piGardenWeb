<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Make the API answer in JSON, always.
 *
 * The log endpoint is called by piGarden's log_send(), a shell curl that does
 * not send an Accept header. Without it Laravel treats the call as a browser
 * request: a bad token returns an HTML error page and a validation failure
 * returns a 302 redirect. piGarden discards the response, so a rejected log
 * vanished silently with no way to tell why.
 *
 * Forcing JSON turns those into 401/422 with a readable body, which is what a
 * client (and anyone debugging with curl) can actually act on.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
