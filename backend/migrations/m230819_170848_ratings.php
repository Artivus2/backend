<?php

use yii\db\Migration;

/**
 * Class m230819_170848_ratings
 */
class m230819_170848_ratings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('{{%ratings_history}}', [
            'id' => $this->primaryKey()->append('AUTO_INCREMENT'),
            'user_id' => $this->Integer()->notNull(),
            'type' => $this->Integer(11)->notNull(),
            'created_at' => $this->timestamp()->notNull(),
            'description' => $this->string(),
            'user_id_rater' => $this->Integer(11)->notNull(),
        ]);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ratings_history}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230819_170848_ratings cannot be reverted.\n";

        return false;
    }
    */
}
