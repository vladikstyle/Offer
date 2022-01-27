<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190310_120000_geoname extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%geoname}}', [
            'geoname_id' => $this->primaryKey(11),
            'name' => $this->string(200)->null()->defaultValue(null),
            'latitude' => $this->decimal(10, 7)->null()->defaultValue(null),
            'longitude' => $this->decimal(10, 7)->null()->defaultValue(null),
            'fclass' => $this->char(1)->null()->defaultValue(null),
            'fcode' => $this->string(10)->null()->defaultValue(null),
            'country' => $this->string(2)->null()->defaultValue(null),
            'population' => $this->integer(11)->null()->defaultValue(null),
            'adm1_geoname_id' => $this->integer(11)->null()->defaultValue(null),
        ], $this->tableOptions);

        $this->createIndex('fclass_idx', '{{%geoname}}', ['fclass']);
        $this->createIndex('fcode_idx', '{{%geoname}}', ['fcode']);
        $this->createIndex('country_idx', '{{%geoname}}', ['country']);
        $this->createIndex('population_idx', '{{%geoname}}', ['population']);
        $this->createIndex('adm1_geoname_id_idx', '{{%geoname}}', ['adm1_geoname_id']);

        $this->addForeignKey('fk_geoname_country',
            '{{%geoname}}', 'country',
            '{{%country}}', 'country',
            $this->cascade, $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_geoname_country', '{{%geoname}}');

        $this->dropIndex('fclass_idx', '{{%geoname}}');
        $this->dropIndex('fcode_idx', '{{%geoname}}');
        $this->dropIndex('country_idx', '{{%geoname}}');
        $this->dropIndex('population_idx', '{{%geoname}}');
        $this->dropIndex('adm1_geoname_id_idx', '{{%geoname}}');

        $this->dropTable('{{%geoname}}');
    }
}
