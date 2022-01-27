<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190412_140000_message_extend extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%message}}', 'text',
            $this->string(1000)->null()->append('COLLATE utf8mb4_bin')
        );
    }

    public function safeDown()
    {
        $this->alterColumn('{{%message}}', 'text',
            $this->string(1000)->notNull()->append('COLLATE utf8mb4_bin')
        );
    }
}
