<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200415_150000_group_post_optimization extends Migration
{
    public function safeUp()
    {
        $this->createIndex('group_post_idx', '{{%group_post}}', ['group_id', 'post_id']);
    }

    public function safeDown()
    {
        $this->dropIndex('group_post_idx', '{{%group_post}}');
    }
}
