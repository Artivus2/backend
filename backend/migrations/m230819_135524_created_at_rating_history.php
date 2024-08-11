<?php

use yii\db\Migration;

/**
 * Class m230819_135524_created_at_rating_history
 */
class m230819_135524_created_at_rating_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('ratings_history', 'created_at', $this->timestamp()->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230819_135524_created_at_rating_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230819_135524_created_at_rating_history cannot be reverted.\n";

        return false;
    }
    */
}
