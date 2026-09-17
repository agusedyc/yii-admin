<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Support;

use function is_array;
use function parse_str;

use Psr\Http\Message\ServerRequestInterface;

use function trim;

/**
 * Helps read request data regardless of whether the body was parsed by middleware.
 */
final class RequestParser
{
    /**
     * @return array<string, mixed>
     */
    public static function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        if (is_array($body)) {
            return $body;
        }

        $raw = (string) $request->getBody();
        if ($raw === '') {
            return [];
        }

        $result = [];
        parse_str($raw, $result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public static function query(ServerRequestInterface $request): array
    {
        return $request->getQueryParams();
    }

    /**
     * Reads a scalar value from the request body. Returns `null` when absent.
     */
    public static function string(ServerRequestInterface $request, string $key): ?string
    {
        $body = self::body($request);
        $value = $body[$key] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function boolFromString(ServerRequestInterface $request, string $key): bool
    {
        $body = self::body($request);

        return isset($body[$key]);
    }
}
