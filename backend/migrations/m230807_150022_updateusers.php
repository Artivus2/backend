<?php

use yii\db\Migration;

/**
 * Class m230807_150022_updateusers
 */
class m230807_150022_updateusers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    $this->addColumn('user', 'created_at', $this->timestamp()->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'created_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230807_150022_updateusers cannot be reverted.\n";

        return false;
    }
    */
}
