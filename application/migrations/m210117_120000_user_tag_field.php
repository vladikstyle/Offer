<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210117_120000_user_tag_field extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'tag', $this->string(255));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'tag');
    }
}
