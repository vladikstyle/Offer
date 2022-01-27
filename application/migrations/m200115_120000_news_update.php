<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200115_120000_news_update extends Migration
{
    public function up()
    {
        $this->addColumn('{{%news}}', 'is_important', $this->boolean()->defaultValue(false)->after('user_id'));
        $this->addColumn('{{%news}}', 'alias', $this->string(255)->after('is_important'));
        $this->addColumn('{{%news}}', 'title', $this->string(255)->after('alias'));
        $this->addColumn('{{%news}}', 'photo_source', $this->string(255)->after('content'));
    }

    public function down()
    {
        $this->dropColumn('{{%news}}', 'is_important');
        $this->dropColumn('{{%news}}', 'alias');
        $this->dropColumn('{{%news}}', 'title');
        $this->dropColumn('{{%news}}', 'photo_source');
    }
}
