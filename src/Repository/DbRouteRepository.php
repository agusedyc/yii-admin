<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Repository;

use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Database backed route metadata repository.
 */
final class DbRouteRepository implements RouteRepositoryInterface
{
    private const TABLE = '{{%admin_route}}';

    public function __construct(private readonly ConnectionInterface $db)
    {
    }

    public function all(): array
    {
        $rows = $this->db
            ->createQuery()
            ->select('*')
            ->from(self::TABLE)
            ->orderBy(['group_name' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $result[] = RouteRecord::fromArray($row);
        }

        return $result;
    }

    public function find(string $name): ?RouteRecord
    {
        $row = $this->db
            ->createQuery()
            ->select('*')
            ->from(self::TABLE)
            ->where(['name' => $name])
            ->one();

        if (!is_array($row)) {
            return null;
        }

        return RouteRecord::fromArray($row);
    }

    public function exists(string $name): bool
    {
        return $this->db
            ->createQuery()
            ->from(self::TABLE)
            ->where(['name' => $name])
            ->exists();
    }

    public function insert(RouteRecord $record): void
    {
        $now = time();

        $this->db->createCommand()->insert(self::TABLE, [
            'name' => $record->getName(),
            'pattern' => $record->getPattern(),
            'methods' => $record->getMethods(),
            'group_name' => $record->getGroup(),
            'alias' => $record->getAlias(),
            'status' => $record->getStatus(),
            'created_at' => $record->getCreatedAt() ?? $now,
            'updated_at' => $record->getUpdatedAt() ?? $now,
        ])->execute();
    }

    public function updateDetails(string $name, string $pattern, string $methods): void
    {
        $this->db->createCommand()->update(self::TABLE, [
            'pattern' => $pattern,
            'methods' => $methods,
            'updated_at' => time(),
        ], ['name' => $name])->execute();
    }

    public function setStatus(string $name, int $status): void
    {
        $this->db->createCommand()->update(self::TABLE, [
            'status' => $status,
            'updated_at' => time(),
        ], ['name' => $name])->execute();
    }

    public function setMeta(string $name, ?string $alias = null, ?string $group = null): void
    {
        $values = ['updated_at' => time()];

        if ($alias !== null) {
            $values['alias'] = $alias;
        }

        if ($group !== null) {
            $values['group_name'] = $group;
        }

        $this->db->createCommand()->update(self::TABLE, $values, ['name' => $name])->execute();
    }

    public function remove(string $name): void
    {
        $this->db->createCommand()->delete(self::TABLE, ['name' => $name])->execute();
    }
}
