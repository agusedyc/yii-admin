<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Repository;

use Agusedyc\Admin\Model\User;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;

final class DefaultUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly ConnectionInterface $db)
    {
    }

    public function findAll(): array
    {
        $rows = (new Query($this->db))->from('{{%admin_user}}')->all();
        $users = [];
        foreach ($rows as $row) {
            $users[] = new User((string)$row['id'], (string)$row['username'], (string)$row['email']);
        }
        return $users;
    }

    public function findById(string $id): ?object
    {
        $row = (new Query($this->db))->from('{{%admin_user}}')->where(['id' => $id])->one();
        if ($row === null || $row === false) {
            return null;
        }
        return new User((string)$row['id'], (string)$row['username'], (string)$row['email']);
    }

    public function save(object $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        $exists = (new Query($this->db))->from('{{%admin_user}}')->where(['id' => $user->id])->exists();
        if ($exists) {
            $this->db->createCommand()->update('{{%admin_user}}', [
                'username' => $user->username,
                'email' => $user->email,
            ], ['id' => $user->id])->execute();
        } else {
            $this->db->createCommand()->insert('{{%admin_user}}', [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ])->execute();
        }
    }

    public function delete(string $id): void
    {
        $this->db->createCommand()->delete('{{%admin_user}}', ['id' => $id])->execute();
    }
}
