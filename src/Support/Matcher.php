<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Support;

use function preg_match;
use function preg_quote;
use function str_replace;

/**
 * Wildcard matching helper. Supports `*` (any characters) and treats `**` as `*`.
 */
final class Matcher
{
    /**
     * @param string[] $patterns
     */
    public static function matchesAny(string $value, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (self::matches($value, (string) $pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function matches(string $value, string $pattern): bool
    {
        if ($pattern === '') {
            return false;
        }

        if ($pattern === $value) {
            return true;
        }

        $pattern = str_replace('**', '*', $pattern);
        $regex = '~^' . str_replace('\*', '.*', preg_quote($pattern, '~')) . '$~';

        return (bool) preg_match($regex, $value);
    }
}
