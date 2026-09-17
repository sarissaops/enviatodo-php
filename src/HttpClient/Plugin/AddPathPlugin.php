<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * Prepends the endpoint base path (e.g. /index.php/) to request paths.
 * AddHostPlugin only sets host/scheme/port and keeps the request path, so
 * without this the base path would be dropped and the API 404s.
 */
final class AddPathPlugin implements Plugin
{
    public function __construct(private readonly string $basePath) {}

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $path = $request->getUri()->getPath();
        $prefix = '/' . trim($this->basePath, '/') . '/';
        if ('' === $this->basePath || '/' === $prefix || str_starts_with($path, $prefix)) {
            return $next($request);
        }

        return $next($request->withUri($request->getUri()->withPath($prefix . ltrim($path, '/'))));
    }
}
