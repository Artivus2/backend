<?php

use yii\db\Migration;

/**
 * Class m230829_130950_p2ppayment
 */
class m230829_130950_p2ppayment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    $this->addColumn('p2p_payment', 'user_id', $this->integer()->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230829_130950_p2ppayment cannot be reverted.\n";

        return false;
    }
    */
}
