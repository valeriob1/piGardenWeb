<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * Read the trusted proxies from the configuration.
     *
     * Left null the middleware ignores X-Forwarded-*, so behind a reverse proxy
     * that terminates TLS the app keeps generating http:// links: the page loads
     * over https and the browser blocks its own assets as mixed content.
     *
     * A comma separated list becomes an array; '*' is passed through as-is
     * (meaning "trust any proxy") and only makes sense when nothing but the
     * proxy can reach the container.
     */
    public function __construct()
    {
        $proxies = config('app.trusted_proxies');

        if (is_string($proxies) && $proxies !== '' && $proxies !== '*') {
            $proxies = array_filter(array_map('trim', explode(',', $proxies)));
        }

        $this->proxies = $proxies ?: null;
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
