<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190310_130000_geoname_translation extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%geoname_translation}}', [
            'alternatename_id' => $this->primaryKey(11),
            'geoname_id' => $this->integer(11)->null()->defaultValue(null),
            'language' => $this->string(7)->null()->defaultValue(null),
            'name' => $this->string(200)->null()->defaultValue(null),
        ], $this->tableOptions);

        $this->createIndex('geoname_idx', '{{%geoname_translation}}', ['geoname_id']);
        $this->createIndex('language_idx', '{{%geoname_translation}}', ['language']);

        $this->addForeignKey('fk_geoname_translation_geoname',
            '{{%geoname_translation}}', 'geoname_id',
            '{{%geoname}}', 'geoname_id',
            $this->cascade, $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_geoname_translation_geoname', '{{%geoname_translation}}');

        $this->dropIndex('geoname_idx', '{{%geoname_translation}}');
        $this->dropIndex('language_idx', '{{%geoname_translation}}');

        $this->dropTable('{{%geoname_translation}}');
    }
}
