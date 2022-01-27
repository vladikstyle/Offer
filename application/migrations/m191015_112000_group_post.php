<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m191015_112000_group_post extends Migration
{
    public function up()
    {
        $this->createTable('{{%group_post}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'post_id' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->addForeignKey('fk_group_post_post',
            '{{%group_post}}', 'post_id',
            '{{%post}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->addForeignKey('fk_group_post_group',
            '{{%group_post}}', 'group_id',
            '{{%group}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->createIndex('group_idx', '{{%group_post}}', 'group_id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_group_post_group', '{{%group_post}}');
        $this->dropForeignKey('fk_group_post_post', '{{%group_post}}');

        $this->dropTable('{{%group_post}}');
    }
}
