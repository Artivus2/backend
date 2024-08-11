<?php

use yii\db\Migration;

/**
 * Class m231228_093920_company_adds
 */
class m231228_093920_company_adds extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company','kpp',$this->string(30)->defaultExpression('null'));
        $this->addColumn('company','fio',$this->string(150)->defaultExpression('null'));
        $this->addColumn('company','phone',$this->string(12)->defaultExpression('null'));
        $this->addColumn('company','bank',$this->string(255)->defaultExpression('null'));
        $this->addColumn('company','bik',$this->string(12)->defaultExpression('null'));
        $this->addColumn('company','rs',$this->string(30)->defaultExpression('null'));
        $this->addColumn('company','ks',$this->string(30)->defaultExpression('null'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231228_093920_company_adds cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231228_093920_company_adds cannot be reverted.\n";

        return false;
    }
    */
}
