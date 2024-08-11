<?php

use yii\db\Migration;

/**
 * Class m230531_121118_add_field_blocked_before_users_table
 */
class m230531_121118_add_field_blocked_before_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'blocked_before', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230531_121118_add_field_blocked_before_users_table cannot be reverted.\n";

        return true;
        //$this->dropColumn('user', 'blocked_before');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230531_121118_add_field_blocked_before_users_table cannot be reverted.\n";

        return false;
    }
    */
}
