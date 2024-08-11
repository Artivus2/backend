<?php

use yii\db\Migration;

/**
 * Class m240301_011839_b2b2
 */
class m240301_011839_b2b2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('b2b_payment','bank', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240301_011839_b2b2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240301_011839_b2b2 cannot be reverted.\n";

        return false;
    }
    */
}
