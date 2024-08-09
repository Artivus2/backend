<?php

namespace app\models\chat;

use Yii;
use app\models\User;

/**
 * This is the model class for table "chat_member".
 *
 * @property int $id
 * @property int $chat_room_id ключ чата
 * @property int $user_id ключ работника
 * @property string $creation_date дата добовления в список чата
 * @property int $status_id
 * @property int $chat_role_id
 *
 * @property ChatRole $chatRole
 * @property ChatRoom $chatRoom
 * @property User $user
 */
class ChatMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_member';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    // public static function getDb()
    // {
    //     return Yii::$app->get('test');
    // }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_room_id', 'user_id', 'creation_date', 'status_id', 'chat_role_id'], 'required'],
            [['chat_room_id', 'user_id', 'status_id', 'chat_role_id'], 'integer'],
            [['creation_date'], 'safe'],
            [['chat_room_id', 'user_id', 'chat_role_id'], 'unique', 'targetAttribute' => ['chat_room_id', 'user_id', 'chat_role_id']],
            [['chat_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatRole::className(), 'targetAttribute' => ['chat_role_id' => 'id']],
            [['chat_room_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatRoom::className(), 'targetAttribute' => ['chat_room_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_room_id' => 'Chat Room ID',
            'user_id' => 'User ID',
            'creation_date' => 'Creation Date',
            'status_id' => 'Status ID',
            'chat_role_id' => 'Chat Role ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatRole()
    {
        return $this->hasOne(ChatRole::className(), ['id' => 'chat_role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatRoom()
    {
        return $this->hasOne(ChatRoom::className(), ['id' => 'chat_room_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
