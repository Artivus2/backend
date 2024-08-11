<?php

use yii\db\Migration;

/**
 * Class m231228_062922_okveds
 */
class m231228_062922_okveds extends Migration
{
    private string $okveds = 'okveds';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->okveds, [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'company_id' => $this->integer(10)->notNull(),
            'active' => $this->integer()->defaultExpression('1')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231228_062922_okveds cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231228_062922_okveds cannot be reverted.\n";

        return false;
    }
    */
}
