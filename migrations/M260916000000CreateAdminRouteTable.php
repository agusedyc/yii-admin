<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

/**
 * Creates the table with route permission display metadata.
 *
 * RBAC items themselves live in the standard `yii_rbac_*` tables provided by `yiisoft/rbac-db`.
 */
final class M260916000000CreateAdminRouteTable implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    private const TABLE = '{{%admin_route}}';

    public function up(MigrationBuilder $b): void
    {
        $b->createTable(
            self::TABLE,
            [
                'name' => 'string(126) NOT NULL PRIMARY KEY',
                'pattern' => 'string(191) NOT NULL DEFAULT \'\'',
                'methods' => 'string(64) NOT NULL DEFAULT \'\'',
                'group_name' => 'string(64) NOT NULL DEFAULT \'\'',
                'alias' => 'string(126) NOT NULL DEFAULT \'\'',
                'status' => 'smallint NOT NULL DEFAULT 1',
                'created_at' => 'integer',
                'updated_at' => 'integer',
            ],
        );

        $b->createIndex(
            table: self::TABLE,
            name: 'idx-admin_route-group_name',
            columns: 'group_name',
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable(self::TABLE);
    }
}
