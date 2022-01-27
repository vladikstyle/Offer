<?php

use hauntd\core\migrations\Migration;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 */
class m190810_180000_oauth extends Migration
{
    public function up()
    {
        $this->createTable('{{%oauth2_client}}', [
            'client_id' => $this->string(80)->notNull(),
            'client_secret' => $this->string(80)->notNull(),
            'redirect_uri' => $this->text()->notNull(),
            'grant_type' => $this->text(),
            'scope' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'PRIMARY KEY (client_id)',
        ], $this->tableOptions);

        $this->createTable('{{%oauth2_access_token}}', [
            'access_token' => $this->string(40)->notNull(),
            'client_id' => $this->string(80)->notNull(),
            'user_id' => $this->integer(),
            'expires' => $this->integer()->notNull(),
            'scope' => $this->text(),
            'PRIMARY KEY (access_token)',
        ], $this->tableOptions);

        $this->createTable('{{%oauth2_refresh_token}}', [
            'refresh_token' => $this->string(40)->notNull(),
            'client_id' => $this->string(80)->notNull(),
            'user_id' => $this->integer(),
            'expires' => $this->integer()->notNull(),
            'scope' => $this->text(),
            'PRIMARY KEY (refresh_token)',
        ], $this->tableOptions);

        $this->createTable('{{%oauth2_authorization_code}}', [
            'authorization_code' => $this->string(40)->notNull(),
            'client_id' => $this->string(80)->notNull(),
            'user_id' => $this->integer(),
            'redirect_uri' => $this->text()->notNull(),
            'expires' => $this->integer()->notNull(),
            'scope' => $this->text(),
            'PRIMARY KEY (authorization_code)',
        ], $this->tableOptions);

        $this->addforeignkey('fk_refresh_token_oauth2_client_client_id', '{{%oauth2_refresh_token}}', 'client_id', '{{%oauth2_client}}', 'client_id', 'cascade', 'cascade');
        $this->addforeignkey('fk_authorization_code_oauth2_client_client_id', '{{%oauth2_authorization_code}}', 'client_id', '{{%oauth2_client}}', 'client_id', 'cascade', 'cascade');
        $this->addforeignkey('fk_access_token_oauth2_client_client_id', '{{%oauth2_access_token}}', 'client_id', '{{%oauth2_client}}', 'client_id', 'cascade', 'cascade');

        $this->createIndex('authorization_code_expires_idx', '{{%oauth2_authorization_code}}', 'expires');
        $this->createIndex('refresh_token_expires_idx', '{{%oauth2_refresh_token}}', 'expires');
        $this->createIndex('access_token_expires_idx', '{{%oauth2_access_token}}', 'expires');

        $this->insert('{{%oauth2_client}}', [
            'client_id' => 'youdate-ionic',
            'client_secret' => 'youdate-ionic',
            'redirect_uri' => 'https://youdate-demo.hauntd.me/',
            'grant_type' => 'client_credentials authorization_code password refresh_token',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down()
    {
        $this->dropForeignKey('fk_refresh_token_oauth2_client_client_id', '{{%oauth2_refresh_token}}');
        $this->dropForeignKey('fk_authorization_code_oauth2_client_client_id', '{{%oauth2_authorization_code}}');
        $this->dropForeignKey('fk_access_token_oauth2_client_client_id', '{{%oauth2_access_token}}');

        $this->dropTable('{{%oauth2_authorization_code}}');
        $this->dropTable('{{%oauth2_refresh_token}}');
        $this->dropTable('{{%oauth2_access_token}}');
        $this->dropTable('{{%oauth2_client}}');
    }
}
