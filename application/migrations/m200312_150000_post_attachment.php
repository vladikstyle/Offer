<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200312_150000_post_attachment extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%post_attachment}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'type' => $this->string(32)->notNull(),
            'data' => $this->text(),
        ], $this->tableOptions);

        $this->createIndex('post_idx', '{{%post_attachment}}', ['post_id']);
        $this->createIndex('type_idx', '{{%post_attachment}}', ['type']);

        $this->addForeignKey('fk_post_attachment_post',
            '{{%post_attachment}}', 'post_id',
            '{{%post}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_post_attachment_post', '{{%post_attachment}}');

        $this->dropTable('{{%post_attachment}}');
    }
}
