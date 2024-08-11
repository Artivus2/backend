<?php

use yii\db\Migration;

/**
 * Class m230906_064534_b2b_history
 */
class m230906_064534_b2b_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('b2b_history', 'creator_id', $this->integer(11)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230906_064534_b2b_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230906_064534_b2b_history cannot be reverted.\n";

        return false;
    }
    */
}
