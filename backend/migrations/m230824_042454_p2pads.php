<?php

use yii\db\Migration;

/**
 * Class m230824_042454_p2pads
 */
class m230824_042454_p2pads extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('p2p_ads','uuid',$this->string(20)->unique());
        $this->addColumn('p2p_ads','duration',$this->integer()-defaultValue(900));
        $this->addColumn('p2p_ads','start_amount',$this->decimal(27,8));
        $this->renameColumn('p2p_ads', 'price', 'amount');

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
        echo "m230824_042454_p2pads cannot be reverted.\n";

        return false;
    }
    */
}
