<?php

use yii\db\Migration;

/**
 * Class m230903_130044_b2b_rename
 */
class m230903_130044_b2b_rename extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('b2b_payment', 'user_id', 'company_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230903_130044_b2b_rename cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230903_130044_b2b_rename cannot be reverted.\n";

        return false;
    }
    */
}
