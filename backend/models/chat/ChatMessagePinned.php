<?php

namespace app\models\chat;

use Yii;

/**
 * This is the model class for table "chat_message_pinned".
 *
 * @property int $id
 * @property int $chat_room_id ключ сообщения
 * @property int $worker_id ключ работника
 * @property int $is_pinned Закреплено сообщение или нет 
 *
 * @property ChatRoom $chatRoom
 * @property Worker $worker
 */
class ChatMessagePinned extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chat_message_pinned';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_room_id', 'worker_id'], 'required'],
            [['chat_room_id', 'worker_id', 'is_pinned'], 'integer'],
            [['chat_room_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatRoom::className(), 'targetAttribute' => ['chat_room_id' => 'id']],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => Worker::className(), 'targetAttribute' => ['worker_id' => 'id']],
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
            'worker_id' => 'Worker ID',
            'is_pinned' => 'Is Pinned',
        ];
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
    public function getWorker()
    {
        return $this->hasOne(Worker::className(), ['id' => 'worker_id']);
    }
}
