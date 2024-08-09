<?php

namespace app\models\chat;

use Yii;

/**
 * This is the model class for table "chat_role".
 *
 * @property int $id
 * @property string $title название роли
 *
 * @property ChatMember[] $chatMembers
 */
class ChatRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_role';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatMembers()
    {
        return $this->hasMany(ChatMember::className(), ['chat_role_id' => 'id']);
    }
}
