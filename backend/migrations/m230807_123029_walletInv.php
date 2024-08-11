<?php

use yii\db\Migration;

/**
 * Class m230807_123029_walletInv
 */
class m230807_123029_walletInv extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%walletInv}}', [
            'id' => $this->primaryKey()->append('AUTO_INCREMENT'),
	    'type' => $this->Integer(11)->notNull(),
            'user_id' => $this->Integer(11)->notNull(),
            'chart_id' => $this->Integer(11)->notNull(),
            'balance' => $this->Decimal(27,8)->notNull()->defaultValue(0.00000000),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%walletInv}}');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230807_123029_walletInv cannot be reverted.\n";

        return false;
    }
    */
}
