<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210117_090000_help extends Migration
{
    public function safeUp()
    {
        // categories
        $this->createTable('{{%help_category}}', [
            'id' => $this->primaryKey(),
            'alias' => $this->string(64)->notNull(),
            'title' => $this->string(64)->notNull(),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_active' => $this->boolean()->defaultValue(1),
            'icon' => $this->string(64),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable('{{%help_category_translation}}', [
            'id' => $this->primaryKey(),
            'help_category_id' => $this->integer()->notNull(),
            'language' => $this->string(6)->notNull(),
            'title' => $this->string(255)->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('help_category_translation_category_idx', '{{%help_category_translation}}', 'help_category_id');
        $this->createIndex('help_category_translation_language_idx', '{{%help_category_translation}}', 'language');

        $this->addForeignKey('{{%fk_help_category_translation_category}}',
            '{{%help_category_translation}}', 'help_category_id',
            '{{%help_category}}', 'id',
            $this->cascade, $this->restrict
        );

        // items
        $this->createTable('{{%help}}', [
            'id' => $this->primaryKey(),
            'help_category_id' => $this->integer(),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_active' => $this->boolean()->defaultValue(1),
            'title' => $this->string(255),
            'content' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable('{{%help_translation}}', [
            'id' => $this->primaryKey(),
            'help_id' => $this->integer()->notNull(),
            'language' => $this->string(6)->notNull(),
            'title' => $this->string(255),
            'content' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $this->tableOptions);

        $this->createIndex('help_translation_help_idx', '{{%help_translation}}', 'help_id');
        $this->createIndex('help_translation_language_idx', '{{%help_translation}}', 'language');

        $this->addForeignKey('{{%fk_help_category}}',
            '{{%help}}', 'help_category_id',
            '{{%help_category}}', 'id',
            $this->cascade, $this->restrict
        );

        $this->addForeignKey('{{%fk_help_translation_help}}',
            '{{%help_translation}}', 'help_id',
            '{{%help}}', 'id',
            $this->cascade, $this->restrict
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_help_category}}', '{{%help}}');
        $this->dropForeignKey('{{%fk_help_translation_help}}', '{{%help_translation}}');
        $this->dropForeignKey('{{%fk_help_category_translation_category}}', '{{%help_category_translation}}');

        $this->dropTable('{{%help_category_translation}}');
        $this->dropTable('{{%help_category}}');

        $this->dropTable('{{%help_translation}}');
        $this->dropTable('{{%help}}');
    }
}
