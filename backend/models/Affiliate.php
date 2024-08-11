<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "affiliate".
 *
 * @property int $id
 * @property int $user_id
 * @property string $code
 */
class Affiliate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'affiliate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'code'], 'required'],
            [['user_id'], 'integer'],
            [['code'], 'string', 'max' => 255],
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
        ];
    }


    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
