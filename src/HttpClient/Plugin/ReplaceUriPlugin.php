<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Debug mode: route every request at the configured endpoint URI.
 */
final class ReplaceUriPlugin implements Plugin
{
    public function __construct(private readonly UriInterface $uri) {}

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($request->withUri($this->uri));
    }
}
