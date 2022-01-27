<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m210117_100000_price extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%price}}', [
            'id' => $this->primaryKey(),
            'credits' => $this->integer()->notNull(),
            'base_price' => $this->decimal(10, 2)->notNull(),
            'discount' => $this->string()->null(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $prices = [
            ['credits' => 50,  'base_price' => 5.00, 'discount' => null],
            ['credits' => 100, 'base_price' => 8.49, 'discount' => null],
            ['credits' => 250, 'base_price' => 20.00, 'discount' => '25%'],
            ['credits' => 500, 'base_price' => 29.99, 'discount' => '35%'],
        ];

        foreach ($prices as $price) {
            $price = new \app\models\Price($price);
            $price->save();
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%price}}');
    }
}
