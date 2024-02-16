<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth_code".
 *
 * @property int $id
 * @property int $user_id
 * @property int $code
 * @property int $date
 * @property int $ip
 */
class AuthCode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'code', 'date'], 'required'],
            [['user_id', 'code', 'date'], 'integer'],
            [['ip'],'string']
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
            'code' => 'Code',
            'date' => 'Date',
            'ip' => 'ip'
        ];
    }
}
