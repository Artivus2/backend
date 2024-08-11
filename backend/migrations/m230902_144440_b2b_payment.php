<?php

use yii\db\Migration;

/**
 * Class m230902_144440_b2b_payment
 */
class m230902_144440_b2b_payment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
            $this->createTable('{{%b2b_payment}}', [
            'id' => $this->primaryKey()->append('AUTO_INCREMENT'),
	    'b2b_ads_id' => $this->Integer(11)->notNull(),
            'payment_id' => $this->Integer(11)->notNull(),
            'user_id' => $this->Integer(11)->notNull()
        ]);
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
        echo "m230902_144440_b2b_payment cannot be reverted.\n";

        return false;
    }
    */
}
