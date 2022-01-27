<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m180601_200001_token extends Migration
{
    public function up()
    {
        $this->createTable('{{%token}}', [
            'user_id' => $this->integer()->notNull(),
            'code' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull(),
        ], $this->tableOptions);

        $this->createIndex('{{%token_unique}}', '{{%token}}', ['user_id', 'code', 'type'], true);
        $this->addForeignKey('{{%fk_user_token}}',
            '{{%token}}', 'user_id',
            '{{%user}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function down()
    {
        $this->dropTable('{{%token}}');
    }
}
