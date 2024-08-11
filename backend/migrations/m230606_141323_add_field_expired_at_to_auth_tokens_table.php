<?php

use yii\db\Migration;

/**
 * Class m230606_141323_add_field_expired_at_to_auth_tokens_table
 */
class m230606_141323_add_field_expired_at_to_auth_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('auth_tokens', 'expired_at', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230606_141323_add_field_expired_at_to_auth_tokens_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230606_141323_add_field_expired_at_to_auth_tokens_table cannot be reverted.\n";

        return false;
    }
    */
}
