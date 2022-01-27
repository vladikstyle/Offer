<?php

use app\models\Account;
use app\models\User;
use app\models\ProfileExtra;
use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210121_100000_geonames_translation_idx extends Migration
{
    public function safeUp()
    {
        $this->createIndex('geoname_unique_idx', '{{%geoname_translation}}', [
            'geoname_id', 'language',
        ], true);
    }

    public function safeDown()
    {
        $this->dropIndex('geoname_unique_idx', '{{%geoname_translation}}');
    }
}
