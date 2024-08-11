<?php

use yii\db\Migration;

/**
 * Class m230903_130818_chart_b2b
 */
class m230903_130818_chart_b2b extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chart', 'b2b', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230903_130818_chart_b2b cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_130818_chart_b2b cannot be reverted.\n";

        return false;
    }
    */
}
