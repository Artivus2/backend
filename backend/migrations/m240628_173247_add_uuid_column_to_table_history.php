<?php

use yii\db\Migration;

/**
 * Class m240628_173247_add_uuid_column_to_table_history
 */
class m240628_173247_add_uuid_column_to_table_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    { 
        $this->addColumn('history', 'uuid', $this->string()->defaultValue(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4))));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m240628_173247_add_uuid_column_to_table_history cannot be reverted.\n";

        // return false;
        $this->dropColumn('history', 'uuid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        
    }
    public function down()
    {
        echo "m240628_173247_add_uuid_column_to_table_history cannot be reverted.\n";

        return false;
    }
    */
}
