<?php

use yii\db\Migration;

/**
 * Class m231219_073044_admin_update
 */
class m231219_073044_admin_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user','app_id',$this->integer(2)->defaultExpression(2));
        $this->addColumn('user','uid',$this->integer(20)->unique());
        $this->addColumn('user','country',$this->string(50)->defaultExpression('null'));
        $this->addColumn('user','city',$this->string()->defaultExpression('null'));
        $this->addColumn('user','is_admin',$this->integer()->defaultExpression(0));
        $this->addColumn('user','affiliate_invitation_id',$this->integer(10));
        $this->addColumn('user','deleted',$this->integer()->defaultExpression(0));
        $this->addColumn('user','banned',$this->integer()->defaultExpression(0));
        $this->addColumn('user','last_visit_time',$this->timestamp()->defaultExpression('null'));
        $this->addColumn('user','confirm_email',$this->timestamp()->defaultExpression('null'));
        $this->addColumn('user','confirm_reset_expire',$this->timestamp()->defaultExpression('null'));
        $this->addColumn('user','confirm_delete_expire',$this->timestamp()->defaultExpression('null'));
        $this->addColumn('user','delete_date',$this->timestamp()->defaultExpression('null'));
        $this->addColumn('user','confirm_email_token',$this->string(36)->defaultExpression('null'));
        $this->addColumn('user','confirm_reset_token',$this->string(36)->defaultExpression('null'));
        $this->addColumn('user','confirm_delete_token',$this->string(36)->defaultExpression('null'));
        $this->addColumn('user','comment',$this->text());


        // 'id' => 'ID', //+
        // 'uid' => 'UID', //add
        // 'app_id' => 'App ID', //add
        // 'email' => 'Почта', //+
        // 'token' => 'Токен', //+
        // 'verify_status' => 'Верификация', //model change
        // 'telegram' => 'Телеграм', //+
        // 'first_name' => 'Имя', //model change
        // 'last_name' => 'Фамилия', //model change
        // 'country' => 'Страна', //add
        // 'city' => 'Город', //add
        // 'password' => 'Пароль', //+
        // 'is_admin' => 'Is Admin', //add
        // 'affiliate_invitation_id' => 'Приглашение партнера', //add
        // 'deleted' => 'Удален', //add
        // 'banned' => 'Заблокирован', //add
        // 'last_visit_time' => 'Время последнего везита', //add
        // 'created_at' => 'Дата регистрации', //model change
        // 'confirm_email' => 'Confirm Email', //add
        // 'confirm_email_token' => 'Confirm Email Token', //add
        // 'confirm_reset_expire' => 'Confirm Reset Expire', //add
        // 'confirm_reset_token' => 'Confirm Reset Token', //add
        // 'confirm_delete_expire' => 'Confirm Delete Expire', //add
        // 'confirm_delete_token' => 'Confirm Delete Token', //add
        // 'delete_date' => 'Дата удаления', //add
        // 'comment' => 'Комментарий', //add
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231219_073044_admin_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231219_073044_admin_update cannot be reverted.\n";

        return false;
    }
    */
}
