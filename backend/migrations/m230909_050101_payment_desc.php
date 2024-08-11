<?php

use yii\db\Migration;

/**
 * Class m230909_050101_payment_desc
 */
class m230909_050101_payment_desc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%payment_desc}}', [
            'id' => $this->primaryKey()->append('AUTO_INCREMENT'),
            'text' => $this->string(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230909_050101_payment_desc cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230909_050101_payment_desc cannot be reverted.\n";

        return false;
    }
    */
}
