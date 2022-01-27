<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210117_130000_searchable_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%profile_field}}', 'searchable', $this->boolean()->defaultValue(false)->after('is_visible'));
        $this->addColumn('{{%profile_field}}', 'searchable_premium', $this->boolean()->defaultValue(false)->after('searchable'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%profile_field}}', 'searchable');
        $this->dropColumn('{{%profile_field}}', 'searchable_premium');
    }
}
