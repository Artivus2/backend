<?php

use yii\db\Migration;

/**
 * Class m230820_143444_chart_update
 */
class m230820_143444_chart_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('chart', 'p2p', $this->integer()->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	$this->DropColumn('chart', 'p2p');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230820_143444_chart_update cannot be reverted.\n";

        return false;
    }
    */
}
