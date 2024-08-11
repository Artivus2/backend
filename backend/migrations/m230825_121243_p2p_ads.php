<?php

use yii\db\Migration;

/**
 * Class m230825_121243_p2p_ads
 */
class m230825_121243_p2p_ads extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->addColumn('p2p_ads','uuid',$this->string(20)->unique());
        $this->addColumn('p2p_ads','duration',$this->integer());
        $this->addColumn('p2p_ads','start_amount',$this->decimal(27,8));
        $this->renameColumn('p2p_ads', 'price', 'amount');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230825_121243_p2p_ads cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230825_121243_p2p_ads cannot be reverted.\n";

        return false;
    }
    */
}
