<?php

use yii\db\Migration;

/**
 * Class m230530_114724_change_filed_fcm_token_auth_tokens_table
 */
class m230530_114724_change_filed_fcm_token_auth_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('auth_tokens', 'fcm_token', $this->string(255)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230530_114724_change_filed_fcm_token_auth_tokens_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230530_114724_change_filed_fcm_token_auth_tokens_table cannot be reverted.\n";

        return false;
    }
    */
}
