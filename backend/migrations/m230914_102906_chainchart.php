<?php

use yii\db\Migration;

/**
 * Class m230914_102906_chainchart
 */
class m230914_102906_chainchart extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chain', 'cryptomus', $this->integer(11)->defaultValue(0));
        $this->addColumn('chart_chain', 'cryptomus', $this->integer(11)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230914_102906_chainchart cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230914_102906_chainchart cannot be reverted.\n";

        return false;
    }
    */
}
