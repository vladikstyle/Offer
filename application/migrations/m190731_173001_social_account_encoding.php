<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190731_173001_social_account_encoding extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%social_account}}', 'data',
            $this->text()->null()->append('COLLATE utf8mb4_bin')
        );
    }

    public function safeDown()
    {
        $this->alterColumn('{{%social_account}}', 'data',
            $this->text()->null()->append('COLLATE utf8_unicode_ci')
        );
    }
}
