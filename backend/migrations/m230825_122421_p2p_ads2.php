<?php

use yii\db\Migration;

/**
 * Class m230825_122421_p2p_ads2
 */
class m230825_122421_p2p_ads2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->renameColumn('p2p_ads', 'min_price', 'min_limit');
        $this->addColumn('p2p_ads','max_limit',$this->decimal(27,8));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230825_122421_p2p_ads2 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230825_122421_p2p_ads2 cannot be reverted.\n";

        return false;
    }
    */
}
