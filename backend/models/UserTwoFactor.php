<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_two_factor".
 *
 * @property int $id
 * @property int $user_id
 * @property string $secret
 * @property int $date
 * @property int $status
 */
class UserTwoFactor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_two_factor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'secret', 'date'], 'required'],
            [['user_id', 'date', 'status'], 'integer'],
            [['secret'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'secret' => 'Secret',
            'date' => 'Date',
            'status' => 'Status',
        ];
    }
}
