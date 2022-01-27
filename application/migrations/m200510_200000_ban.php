<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200510_200000_ban extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%ban}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(32)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('ip_idx', '{{%ban}}', ['ip']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%ban}}');
    }
}
