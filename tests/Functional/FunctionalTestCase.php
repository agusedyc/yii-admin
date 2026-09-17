<?php

declare(strict_types=1);

namespace Agusedyc\Admin\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection as SqliteConnection;
use Yiisoft\Db\Sqlite\Driver;

abstract class FunctionalTestCase extends TestCase
{
    protected ConnectionInterface $db;

    protected function setUp(): void
    {
        $driver = new Driver('sqlite::memory:');
        $this->db = new SqliteConnection($driver, new SchemaCache(new ArrayCache()));
        $this->db->open();
    }

    protected function createRbacSchema(): void
    {
        $this->execute('
            CREATE TABLE yii_rbac_item (
                name VARCHAR(126) NOT NULL PRIMARY KEY,
                type VARCHAR(10) NOT NULL,
                description VARCHAR(191),
                rule_name VARCHAR(64),
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            )
        ');

        $this->execute('
            CREATE TABLE yii_rbac_item_child (
                parent VARCHAR(126) NOT NULL,
                child VARCHAR(126) NOT NULL,
                PRIMARY KEY (parent, child),
                FOREIGN KEY (parent) REFERENCES yii_rbac_item (name),
                FOREIGN KEY (child) REFERENCES yii_rbac_item (name)
            )
        ');

        $this->execute('
            CREATE TABLE yii_rbac_assignment (
                item_name VARCHAR(126) NOT NULL,
                user_id VARCHAR(126) NOT NULL,
                created_at INTEGER NOT NULL,
                PRIMARY KEY (item_name, user_id)
            )
        ');
    }

    protected function createRouteSchema(): void
    {
        $this->execute('
            CREATE TABLE admin_route (
                name VARCHAR(126) NOT NULL PRIMARY KEY,
                pattern VARCHAR(191) NOT NULL DEFAULT \'\',
                methods VARCHAR(64) NOT NULL DEFAULT \'\',
                group_name VARCHAR(64) NOT NULL DEFAULT \'\',
                alias VARCHAR(126) NOT NULL DEFAULT \'\',
                status SMALLINT NOT NULL DEFAULT 1,
                created_at INTEGER,
                updated_at INTEGER
            )
        ');
    }

    protected function execute(string $sql): void
    {
        $this->db->createCommand($sql)->execute();
    }
}
