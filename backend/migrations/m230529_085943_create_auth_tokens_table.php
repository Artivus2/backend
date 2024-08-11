<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%auth_tokens}}`.
 */
class m230529_085943_create_auth_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%auth_tokens}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger(),
            'token' => $this->string(),
            'fcm_token' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auth_tokens}}');
    }
}
