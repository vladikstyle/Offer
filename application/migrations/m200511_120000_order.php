<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200511_120000_order extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'guid' => $this->char(36)->notNull(),
            'user_id' => $this->integer(),
            'currency' => $this->string(3)->notNull(),
            'total_price' => $this->decimal(10, 2)->notNull(),
            'amount' => $this->integer()->defaultValue(0),
            'status' => $this->string(32),
            'payment_method' => $this->string(32),
            'payment_id' => $this->string(255),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('order_user_idx', '{{%order}}', 'user_id');
        $this->createIndex('order_status_idx', '{{%order}}', 'status');
        $this->createIndex('order_guid_idx', '{{%order}}', 'guid', true);

        $this->addForeignKey('{{%fk_order_user}}',
            '{{%order}}', 'user_id',
            '{{%user}}', 'id',
            'set null', $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_order_user}}', '{{%order}}');

        $this->dropTable('{{%order}}');
    }
}
