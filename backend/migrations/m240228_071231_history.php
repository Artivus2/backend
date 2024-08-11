<?php

use yii\db\Migration;

/**
 * Class m240228_071231_history
 */
class m240228_071231_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('history', 'ipn_id', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240228_071231_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240228_071231_history cannot be reverted.\n";

        return false;
    }
    */
}
