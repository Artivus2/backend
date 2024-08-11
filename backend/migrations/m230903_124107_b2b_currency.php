<?php

use yii\db\Migration;

/**
 * Class m230903_124107_b2b_currency
 */
class m230903_124107_b2b_currency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('b2b_ads','currency_id',$this->Integer(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230903_124107_b2b_currency cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_124107_b2b_currency cannot be reverted.\n";

        return false;
    }
    */
}
