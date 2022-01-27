<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190225_102001_data_request extends Migration
{
    public function up()
    {
        $this->createTable('{{%data_request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'code' => $this->string(255)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('data_user_idx', '{{%data_request}}', 'user_id');
        $this->createIndex('data_status_idx', '{{%data_request}}', 'status');
        $this->createIndex('data_code_idx', '{{%data_request}}', 'code');

        $this->addForeignKey('fk_data_request_user',
            '{{%data_request}}', 'user_id',
            '{{%user}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_data_request_user', '{{%data_request}}');

        $this->dropTable('{{%data_request}}');
    }
}
