<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190731_182000_news extends Migration
{
    public function up()
    {
        $this->createTable('{{%news}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull(), // draft, published
            'excerpt' => $this->text(),
            'content' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('news_user_idx', '{{%news}}', 'user_id');
        $this->createIndex('news_status_idx', '{{%news}}', 'status');

        $this->addForeignKey('fk_news_user',
            '{{%news}}', 'user_id',
            '{{%user}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_news_user', '{{%news}}');

        $this->dropTable('{{%news}}');
    }
}
