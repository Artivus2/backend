<?php

use yii\db\Migration;

/**
 * Class m230914_094019_cryptomys_update
 */
class m230914_094019_cryptomys_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chart', 'cryptomus', $this->integer(11)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230914_094019_cryptomys_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230914_094019_cryptomys_update cannot be reverted.\n";

        return false;
    }
    */
}
