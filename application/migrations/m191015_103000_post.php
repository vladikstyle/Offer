<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m191015_103000_post extends Migration
{
    public function up()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'content' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->addforeignkey('fk_post_user',
            '{{%post}}', 'user_id',
            '{{%user}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->createIndex('post_user_idx', '{{%post}}', 'user_id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_post_user', '{{%post}}');

        $this->dropTable('{{%post}}');
    }
}
