<?php

use yii\db\Migration;

/**
 * Class m230903_155025_b2b_history
 */
class m230903_155025_b2b_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('b2b_history', 'file_path', $this->string(255)->notNull());
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
        echo "m230903_155025_b2b_history cannot be reverted.\n";

        return false;
    }
    */
}
