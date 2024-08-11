<?php

use yii\db\Migration;

/**
 * Class m230902_142705_b2b
 */
class m230902_142705_b2b extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('b2b_ads','duration',$this->integer());
        $this->addColumn('b2b_ads','start_amount',$this->decimal(27,8));
        $this->renameColumn('b2b_ads', 'price', 'amount');
	$this->renameColumn('b2b_ads', 'min_price', 'min_limit');
        $this->addColumn('b2b_ads','max_limit',$this->decimal(27,8));
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
        echo "m230902_142705_b2b cannot be reverted.\n";

        return false;
    }
    */
}
