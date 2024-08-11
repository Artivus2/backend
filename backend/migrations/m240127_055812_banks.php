<?php

use yii\db\Migration;

/**
 * Class m240127_055812_banks
 */
class m240127_055812_banks extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('banks', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'bik' => $this->string(9)->notNull(),
	    'address' => $this->string(255)->notNull(),
            'active' => $this->integer()->defaultExpression('1')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240127_055812_banks cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240127_055812_banks cannot be reverted.\n";

        return false;
    }
    */
}
