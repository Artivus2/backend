<?php

use yii\db\Migration;

/**
 * Class m230902_133850_paymentactive
 */
class m230902_133850_paymentactive extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('payment_user', 'active', $this->integer()->notNull()->defaultValue(1));

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
        echo "m230902_133850_paymentactive cannot be reverted.\n";

        return false;
    }
    */
}
