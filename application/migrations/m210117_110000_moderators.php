<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210117_110000_moderators extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%admin}}', 'role', $this->string()->notNull()->defaultValue(\app\models\Admin::ROLE_ADMIN));
        $this->addColumn('{{%admin}}', 'permissions', $this->text());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%admin}}', 'permissions');
        $this->dropColumn('{{%admin}}', 'role');
    }
}
