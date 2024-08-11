<?php

use yii\db\Migration;

/**
 * Class m231220_080602_status
 */
class m231220_080602_status extends Migration
{
    
    private string $status_type = 'status_type';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->status_type, [
            'id' => $this->primaryKey(),
            'status_id' => $this->integer(2)->notNull(),
            'title' => $this->string(255)->notNull(),
            'active' => $this->integer()->defaultExpression('1')
        ]);

        $this->insert($this->status_type, [
            'status_id' => -1,
            'title' => 'Создан',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 1,
            'title' => 'Торгуется',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 2,
            'title' => 'Оплачен',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 4,
            'title' => 'Частично исполнен',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 5,
            'title' => 'В аппеляции',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 6,
            'title' => 'Отменен',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 7,
            'title' => 'Отменен системой',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 8,
            'title' => 'Заблокирован',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 9,
            'title' => 'Удален',
            'active' => 1,
        ]);
        $this->insert($this->status_type, [
            'status_id' => 10,
            'title' => 'Полностью исполнен',
            'active' => 1,
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231220_080602_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231220_080602_status cannot be reverted.\n";

        return false;
    }
    */
}
