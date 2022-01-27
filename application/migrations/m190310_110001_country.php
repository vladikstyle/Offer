<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190310_110001_country extends Migration
{
    public function up()
    {
        $this->createTable('{{%country}}', [
            'country' => $this->string(2),
            'name' => $this->string(255)->notNull(),
            'geoname_id' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createTable('{{%country_translation}}', [
            'country' => $this->string(2),
            'language' => $this->string(6)->notNull(),
            'translation' => $this->string(255)->notNull()->append('COLLATE utf8mb4_general_ci'),
        ], $this->tableOptions);

        $this->createIndex('country_idx', '{{%country}}', 'country', true);
        $this->createIndex('country_geoname_idx', '{{%country}}', 'geoname_id', true);
        $this->createIndex('country_idx', '{{%country_translation}}', 'country');
        $this->createIndex('country_translation_idx', '{{%country_translation}}', 'country');
        $this->createIndex('country_translation_language_idx', '{{%country_translation}}', 'language');
        $this->createIndex('country_translation_unique_idx', '{{%country_translation}}', [
            'country', 'language',
        ], true);

        $this->addForeignKey('fk_country_translation_country',
            '{{%country_translation}}', 'country',
            '{{%country}}', 'country',
            $this->cascade, $this->restrict
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_country_translation_country', '{{%country_translation}}');

        $this->dropTable('{{%country_translation}}');
        $this->dropTable('{{%country}}');
    }
}
