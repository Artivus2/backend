<?php

use yii\db\Migration;

/**
 * Class m231220_034424_wallet_type
 */
class m231220_034424_wallet_type extends Migration
{
    
    private string $wallet_type = 'wallet_type';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->wallet_type, [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'active' => $this->integer(1)->notNull(),
        ]);

        $this->insert($this->wallet_type, [
            'title' => 'Финансовый',
            'active' => 1,
        ]);
        $this->insert($this->wallet_type, [
            'title' => 'Спотовый',
            'active' => 1,
        ]);
        $this->insert($this->wallet_type, [
            'title' => 'Маржинальный',
            'active' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231220_034424_wallet_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231220_034424_wallet_type cannot be reverted.\n";

        return false;
    }
    */
}
