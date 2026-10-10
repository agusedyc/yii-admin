<?php

declare(strict_types=1);

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

final class m231010_000000_create_admin_user_table implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%admin_user}}', [
            'id' => $b->string(64)->notNull()->primaryKey(),
            'username' => $b->string(255)->notNull(),
            'email' => $b->string(255)->notNull(),
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%admin_user}}');
    }
}
