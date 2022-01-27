<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190913_090000_group_members_count extends Migration
{
    public function up()
    {
        $this->addColumn('{{%group}}', 'members_count', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('{{%group}}', 'members_count');
    }
}
