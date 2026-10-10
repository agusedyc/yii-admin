<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Repository;

interface UserRepositoryInterface
{
    public function findAll(): array;
    public function findById(string $id): ?object;
    public function save(object $user): void;
    public function delete(string $id): void;
}
