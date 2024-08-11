<?php

use yii\db\Migration;

/**
 * Class m230914_074720_cryptomus
 */
class m230914_074720_cryptomus extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('{{%cryptomus}}', [
            'id' => $this->primaryKey()->append('AUTO_INCREMENT'),
	    'symbol' => $this->string(12)->notNull(),
            'network' => $this->string(12)->notNull(),
            'active' => $this->Integer(11)->notNull()->defaultValue(1),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230914_074720_cryptomus cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230914_074720_cryptomus cannot be reverted.\n";

        return false;
    }
    */
}
