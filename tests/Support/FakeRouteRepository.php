<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Support;

use Agusedyc\Admin\Repository\RouteRecord;
use Agusedyc\Admin\Repository\RouteRepositoryInterface;

final class FakeRouteRepository implements RouteRepositoryInterface
{
    /** @var RouteRecord[] */
    private array $records = [];

    public function __construct(array $records = [])
    {
        foreach ($records as $record) {
            $this->records[$record->getName()] = $record;
        }
    }

    public function all(): array
    {
        return array_values($this->records);
    }

    public function find(string $name): ?RouteRecord
    {
        return $this->records[$name] ?? null;
    }

    public function exists(string $name): bool
    {
        return isset($this->records[$name]);
    }

    public function setRecords(array $records): void
    {
        $this->records = [];
        foreach ($records as $record) {
            $this->records[$record->getName()] = $record;
        }
    }

    public function insert(RouteRecord $record): void
    {
        $this->records[$record->getName()] = $record;
    }

    public function updateDetails(string $name, string $pattern, string $methods): void
    {
        if (!isset($this->records[$name])) {
            return;
        }

        $record = $this->records[$name];
        $this->records[$name] = new RouteRecord(
            $record->getName(),
            $pattern,
            $methods,
            $record->getGroup(),
            $record->getAlias(),
            $record->getStatus(),
            $record->getCreatedAt(),
            time(),
        );
    }

    public function setStatus(string $name, int $status): void
    {
        if (!isset($this->records[$name])) {
            return;
        }

        $record = $this->records[$name];
        $this->records[$name] = new RouteRecord(
            $record->getName(),
            $record->getPattern(),
            $record->getMethods(),
            $record->getGroup(),
            $record->getAlias(),
            $status,
            $record->getCreatedAt(),
            time(),
        );
    }

    public function setMeta(string $name, ?string $alias = null, ?string $group = null): void
    {
        if (!isset($this->records[$name])) {
            return;
        }

        $record = $this->records[$name];
        $this->records[$name] = new RouteRecord(
            $record->getName(),
            $record->getPattern(),
            $record->getMethods(),
            $group ?? $record->getGroup(),
            $alias ?? $record->getAlias(),
            $record->getStatus(),
            $record->getCreatedAt(),
            time(),
        );
    }

    public function remove(string $name): void
    {
        unset($this->records[$name]);
    }
}
