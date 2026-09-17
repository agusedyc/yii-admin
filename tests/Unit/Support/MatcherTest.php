<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Unit\Support;

use Agusedyc\Admin\Support\Matcher;
use PHPUnit\Framework\TestCase;

final class MatcherTest extends TestCase
{
    public function testExactMatch(): void
    {
        self::assertTrue(Matcher::matches('admin/role/index', 'admin/role/index'));
        self::assertFalse(Matcher::matches('admin/role/index', 'admin/role'));
    }

    public function testWildcardMatch(): void
    {
        self::assertTrue(Matcher::matches('admin/role/index', 'admin/*'));
        self::assertTrue(Matcher::matches('admin/role/index', 'admin/**'));
        self::assertTrue(Matcher::matches('admin/role/index', '*'));
        self::assertFalse(Matcher::matches('admin/role/index', 'public/*'));
    }

    public function testMatchesAny(): void
    {
        self::assertTrue(Matcher::matchesAny('home', ['admin/*', 'home']));
        self::assertFalse(Matcher::matchesAny('home', ['admin/*']));
    }

    public function testEmptyPatternNeverMatches(): void
    {
        self::assertFalse(Matcher::matches('home', ''));
    }
}
