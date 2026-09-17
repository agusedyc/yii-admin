<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Support;

use function implode;

use Stringable;
use Yiisoft\Router\UrlGeneratorInterface;

final class UrlGeneratorStub implements UrlGeneratorInterface
{
    /** @var array<string, scalar|Stringable|null> */
    private array $defaults = [];

    public function generate(
        string $name,
        array $arguments = [],
        array $queryParameters = [],
        ?string $hash = null,
    ): string {
        $query = $queryParameters === [] ? '' : '?' . implode('&', array_map(strval(...), $queryParameters));

        return '/admin/' . $name . $query;
    }

    public function generateAbsolute(
        string $name,
        array $arguments = [],
        array $queryParameters = [],
        ?string $hash = null,
        ?string $scheme = null,
        ?string $host = null,
    ): string {
        return 'https://localhost' . $this->generate($name, $arguments, $queryParameters, $hash);
    }

    public function generateFromCurrent(
        array $replacedArguments,
        array $queryParameters = [],
        ?string $hash = null,
        ?string $fallbackRouteName = null,
    ): string {
        return $this->generate($fallbackRouteName ?? 'current', $replacedArguments, $queryParameters, $hash);
    }

    public function getUriPrefix(): string
    {
        return '';
    }

    public function setUriPrefix(string $name): void
    {
    }

    public function setDefaultArgument(string $name, bool|float|int|string|Stringable|null $value): void
    {
        $this->defaults[$name] = $value;
    }
}
