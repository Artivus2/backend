<?php

use yii\db\Migration;

/**
 * Class m230820_132633_chartupdate
 */
class m230820_132633_chartupdate extends Migration
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

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230820_132633_chartupdate cannot be reverted.\n";

        return false;
    }
    */
}
