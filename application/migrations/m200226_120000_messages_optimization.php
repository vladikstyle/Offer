<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m200226_120000_messages_optimization extends Migration
{
    public function up()
    {
        $this->createIndex('message_idx1_idx', '{{%message}}', [
            'to_user_id', 'is_deleted_by_receiver',
        ]);

        $this->createIndex('message_idx2_idx', '{{%message}}', [
            'from_user_id', 'is_deleted_by_sender',
        ]);

        $this->createIndex('message_idx3_idx', '{{%message}}', [
            'from_user_id', 'to_user_id', 'is_deleted_by_receiver',
        ]);

        $this->createIndex('message_idx4_idx', '{{%message}}', [
            'from_user_id', 'to_user_id', 'is_deleted_by_sender',
        ]);
    }

    public function down()
    {
        $this->dropIndex('message_idx1_idx', '{{%message}}');
        $this->dropIndex('message_idx2_idx', '{{%message}}');
        $this->dropIndex('message_idx3_idx', '{{%message}}');
        $this->dropIndex('message_idx4_idx', '{{%message}}');
    }
}
