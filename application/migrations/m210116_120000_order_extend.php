<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210116_120000_order_extend extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'callback_at', $this->integer()->null()->after('updated_at'));

    }

    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'callback_at');
    }
}
