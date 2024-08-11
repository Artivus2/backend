<?php

namespace app\models\chat;

use Yii;

/**
 * This is the model class for table "chat_type".
 *
 * @property int $id
 * @property string $title название типа чата
 *
 * @property ChatRoom[] $chatRooms
 */
class ChatType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_type';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 45],
            [['title'], 'unique'],
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
    public function getChatRooms()
    {
        return $this->hasMany(ChatRoom::className(), ['chat_type_id' => 'id']);
    }
}
