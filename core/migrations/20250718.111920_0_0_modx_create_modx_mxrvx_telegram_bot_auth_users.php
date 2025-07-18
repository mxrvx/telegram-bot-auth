<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmModxBc5ecb4f9f78eca7d9f4e4d7fd9ad1b0 extends Migration
{
    protected const DATABASE = 'modx';

    public function up(): void
    {
        $this->table('mxrvx_telegram_bot_auth_users')
            ->addColumn('id', 'bigInteger', [
                'nullable' => false,
                'defaultValue' => null,
                'size' => 20,
                'autoIncrement' => false,
                'unsigned' => true,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('username', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 191, 'comment' => ''])
            ->addColumn('phone', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 100, 'comment' => ''])
            ->addColumn('email', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 100, 'comment' => ''])
            ->addColumn('surname', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 100, 'comment' => ''])
            ->addColumn('name', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 100, 'comment' => ''])
            ->addColumn('patronymic', 'string', ['nullable' => false, 'defaultValue' => '', 'size' => 100, 'comment' => ''])
            ->addColumn('user_id', 'integer', [
                'nullable' => false,
                'defaultValue' => 0,
                'size' => 11,
                'autoIncrement' => false,
                'unsigned' => true,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('is_valid', 'boolean', [
                'nullable' => false,
                'defaultValue' => false,
                'size' => 1,
                'autoIncrement' => false,
                'unsigned' => false,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('is_valid_phone', 'boolean', [
                'nullable' => false,
                'defaultValue' => false,
                'size' => 1,
                'autoIncrement' => false,
                'unsigned' => false,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('is_valid_email', 'boolean', [
                'nullable' => false,
                'defaultValue' => false,
                'size' => 1,
                'autoIncrement' => false,
                'unsigned' => false,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('is_valid_fullname', 'boolean', [
                'nullable' => false,
                'defaultValue' => false,
                'size' => 1,
                'autoIncrement' => false,
                'unsigned' => false,
                'zerofill' => false,
                'comment' => '',
            ])
            ->addColumn('created_at', 'datetime', ['nullable' => false, 'defaultValue' => 'CURRENT_TIMESTAMP', 'comment' => ''])
            ->addColumn('updated_at', 'datetime', ['nullable' => true, 'defaultValue' => null, 'comment' => ''])
            ->addIndex(['id'], ['name' => 'id', 'unique' => true])
            ->addIndex(['user_id'], ['name' => 'user_id', 'unique' => false])
            ->addIndex(['phone'], ['name' => 'phone', 'unique' => false])
            ->addIndex(['email'], ['name' => 'email', 'unique' => false])
            ->addIndex(['created_at'], ['name' => 'created_at', 'unique' => false])
            ->addIndex(['updated_at'], ['name' => 'updated_at', 'unique' => false])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('mxrvx_telegram_bot_auth_users')->drop();
    }
}
