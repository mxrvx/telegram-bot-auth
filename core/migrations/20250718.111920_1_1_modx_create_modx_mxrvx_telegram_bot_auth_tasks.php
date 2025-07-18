<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmModxFdb12e08a814f50794fb66d01a9f4b17 extends Migration
{
    protected const DATABASE = 'modx';

    public function up(): void
    {
        $this->table('mxrvx_telegram_bot_auth_tasks')
            ->addColumn('session_id', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 191, 'comment' => ''])
            ->addColumn('uuid', 'string', ['nullable' => false, 'defaultValue' => null, 'size' => 36, 'comment' => ''])
            ->addColumn('telegram_id', 'bigInteger', [
                'nullable' => true,
                'defaultValue' => null,
                'size' => 20,
                'autoIncrement' => false,
                'unsigned' => true,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('command', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 191, 'comment' => ''])
            ->addColumn('is_success', 'boolean', [
                'nullable' => false,
                'defaultValue' => false,
                'size' => 1,
                'autoIncrement' => false,
                'unsigned' => false,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('config', 'json', ['nullable' => true, 'defaultValue' => null, 'comment' => ''])
            ->addColumn('data', 'json', ['nullable' => true, 'defaultValue' => null, 'comment' => ''])
            ->addColumn('updated_at', 'datetime', ['nullable' => true, 'defaultValue' => null, 'comment' => ''])
            ->addIndex(['telegram_id'], ['name' => 'telegram_id', 'unique' => false])
            ->addIndex(['session_id'], ['name' => 'session_id', 'unique' => false])
            ->addIndex(['uuid'], ['name' => 'uuid', 'unique' => true])
            ->addIndex(['command'], ['name' => 'command', 'unique' => false])
            ->addIndex(['updated_at'], ['name' => 'updated_at', 'unique' => false])
            ->addForeignKey(['telegram_id'], 'mxrvx_telegram_bot_auth_users', ['id'], [
                'name' => '7d33861056a525f35421000469a221a2',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'indexCreate' => true,
            ])
            ->setPrimaryKeys(['session_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('mxrvx_telegram_bot_auth_tasks')->drop();
    }
}
