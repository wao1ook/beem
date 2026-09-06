<?php

declare(strict_types=1);

namespace Emanate\BeemSms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates inbound Beem callbacks.
 *
 * Beem's guidance is to "validate the API Key (or other auth token) on every
 * request before processing the payload", but it does not fix a header name.
 * Three shapes are accepted, and the first configured one that matches wins:
 *
 *  - HTTP Basic auth carrying the API key and secret
 *  - an Authorization header equal to the configured access token
 *  - an Authorization header equal to a dedicated callback token
 *
 * @see https://docs.beem.africa/guides/two-way-sms/security
 */
class VerifyBeemCallback
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ( ! config('beem.two_way.verify_credentials', true)) {
            return $next($request);
        }

        if ($this->isAuthenticated($request)) {
            return $next($request);
        }

        return response()->json(['successful' => false, 'message' => 'Unauthorized'], 401);
    }

    /**
     * Whether the request carries credentials matching the configured ones.
     */
    protected function isAuthenticated(Request $request): bool
    {
        $header = (string) $request->header('Authorization', '');

        $callbackToken = config('beem.two_way.token');

        if (is_string($callbackToken) && $callbackToken !== '' && $this->matches($header, $callbackToken)) {
            return true;
        }

        $accessToken = config('beem.access_token');

        if (is_string($accessToken) && $accessToken !== '' && $this->matches($header, $accessToken)) {
            return true;
        }

        return $this->matchesBasicAuth($request);
    }

    /**
     * Whether the request carries Basic auth matching the API key and secret.
     */
    protected function matchesBasicAuth(Request $request): bool
    {
        $apiKey = config('beem.api_key');
        $secretKey = config('beem.secret_key');

        if ( ! is_string($apiKey) || $apiKey === '' || ! is_string($secretKey) || $secretKey === '') {
            return false;
        }

        return $this->matches((string) $request->getUser(), $apiKey)
            && $this->matches((string) $request->getPassword(), $secretKey);
    }

    /**
     * Compare two values in constant time, tolerating a Bearer or Basic prefix.
     */
    protected function matches(string $provided, string $expected): bool
    {
        foreach (['Bearer ', 'bearer ', 'Token ', 'token '] as $prefix) {
            if (str_starts_with($provided, $prefix)) {
                $provided = mb_substr($provided, mb_strlen($prefix));

                break;
            }
        }

        return $provided !== '' && hash_equals($expected, $provided);
    }
}
