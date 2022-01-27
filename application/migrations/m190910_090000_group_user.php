<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190910_090000_group_user extends Migration
{
    public function up()
    {
        $this->createTable('{{%group_user}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(64),
            'role' => $this->string(64),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->addforeignkey('fk_group_user_user',
            '{{%group_user}}', 'user_id',
            '{{%user}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->addforeignkey('fk_group_user_group',
            '{{%group_user}}', 'group_id',
            '{{%group}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->createIndex('group_user_user_idx', '{{%group_user}}', 'user_id');
        $this->createIndex('group_user_group_idx', '{{%group_user}}', 'group_id');
        $this->createIndex('group_user_group_unique_idx', '{{%group_user}}', ['group_id', 'user_id'], true);
    }

    public function down()
    {
        $this->dropForeignKey('fk_group_user_user', '{{%group_user}}');
        $this->dropForeignKey('fk_group_user_group', '{{%group_user}}');

        $this->dropTable('{{%group_user}}');
    }
}
