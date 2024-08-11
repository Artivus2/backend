<?php

use yii\db\Migration;

/**
 * Class m230613_164501_add_field_payment_
 */
class m230613_164501_add_field_payment_ extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('payment_user', 'payment_receiver', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230613_164501_add_field_payment_ cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230613_164501_add_field_payment_ cannot be reverted.\n";

        return false;
    }
    */
}
