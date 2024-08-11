<?php

use yii\db\Migration;

/**
 * Class m230902_143951_b2b_uuid
 */
class m230902_143951_b2b_uuid extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('b2b_ads','uuid',$this->string(20)->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230902_143951_b2b_uuid cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230902_143951_b2b_uuid cannot be reverted.\n";

        return false;
    }
    */
}
