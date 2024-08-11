<?php

use yii\db\Migration;

/**
 * Class m230909_050243_p2p_history_add
 */
class m230909_050243_p2p_history_add extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('p2p_history', 'description_id', $this->integer(11)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230909_050243_p2p_history_add cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230909_050243_p2p_history_add cannot be reverted.\n";

        return false;
    }
    */
}
