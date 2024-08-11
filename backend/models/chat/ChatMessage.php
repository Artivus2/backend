<?php

namespace app\models\chat;

use Yii;

/**
 * This is the model class for table "chat_message".
 *
 * @property int $id
 * @property string|null $primary_message Текст сообщения
 * @property int|null $sender_user_id Ключ работника, отправившего сообщение
 * @property int $chat_room_id Ключ чата
 * @property int|null $chat_attachment_type_id Ключ типа вложения в сообщении
 * @property string|null $attachment Вложение сообщения
 * @property string $date_time Дата создания сообщения
 *
 * @property ChatAttachmentType $chatAttachmentType
 * @property ChatRoom $chatRoom
 * @property ChatMessagePinned[] $chatMessagePinneds
 * @property User[] $users
 * @property ChatMessageReciever[] $chatMessageRecievers
 */
class ChatMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_message';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sender_user_id', 'chat_room_id', 'chat_attachment_type_id'], 'integer'],
            [['chat_room_id', 'date_time'], 'required'],
            [['date_time'], 'safe'],
            [['primary_message'], 'string', 'max' => 400],
            [['attachment'], 'string', 'max' => 350],
            [['chat_attachment_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatAttachmentType::className(), 'targetAttribute' => ['chat_attachment_type_id' => 'id']],
            [['chat_room_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatRoom::className(), 'targetAttribute' => ['chat_room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'primary_message' => 'Primary Message',
            'sender_user_id' => 'Sender User ID',
            'chat_room_id' => 'Chat Room ID',
            'chat_attachment_type_id' => 'Chat Attachment Type ID',
            'attachment' => 'Attachment',
            'date_time' => 'Date Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatAttachmentType()
    {
        return $this->hasOne(ChatAttachmentType::className(), ['id' => 'chat_attachment_type_id']);
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
    public function getChatMessagePinneds()
    {
        return $this->hasMany(ChatMessagePinned::className(), ['chat_message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('chat_message_pinned', ['chat_message_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatMessageRecievers()
    {
        return $this->hasMany(ChatMessageReciever::className(), ['chat_message_id' => 'id', 'chat_message_chat_room_id' => 'chat_room_id']);
    }
}
