<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190412_150000_message_attachment extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%message_attachment}}', [
            'id' => $this->primaryKey(),
            'message_id' => $this->bigInteger(20)->notNull()->unsigned(),
            'type' => $this->string(32)->notNull(),
            'data' => $this->text(),
        ], $this->tableOptions);

        $this->createIndex('message_idx', '{{%message_attachment}}', ['message_id']);
        $this->createIndex('type', '{{%message_attachment}}', ['type']);

        $this->addForeignKey('fk_message_attachment_message',
            '{{%message_attachment}}', 'message_id',
            '{{%message}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_message_attachment_message', '{{%message_attachment}}');

        $this->dropTable('{{%message_attachment}}');
    }
}
