<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190910_080000_group extends Migration
{
    public function up()
    {
        $this->createTable('{{%group}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'alias' => $this->string(128)->notNull(),
            'is_verified' => $this->boolean()->defaultValue(false),
            'title' => $this->string(255)->notNull(),
            'description' => $this->string(255)->notNull(),
            'photo_path' => $this->string(500),
            'cover_path' => $this->string(500),
            'visibility' => $this->string(64),
            'country' => $this->string(2),
            'city' => $this->integer()->unsigned(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->addforeignkey('fk_group_user',
            '{{%group}}', 'user_id',
            '{{%user}}', 'id',
            'set null', $this->restrict
        );

        $this->createIndex('group_user_idx', '{{%group}}', 'user_id');
        $this->createIndex('group_visibility_idx', '{{%group}}', 'visibility');
        $this->createIndex('group_verified_idx', '{{%group}}', 'is_verified');
    }

    public function down()
    {
        $this->dropForeignKey('fk_group_user', '{{%group}}');

        $this->dropTable('{{%group}}');
    }
}
