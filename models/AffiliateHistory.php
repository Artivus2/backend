<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "affiliate_history".
 *
 * @property int $id
 * @property int $affiliate_id
 * @property int $user_id
 * @property int $date
 */
class AffiliateHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'affiliate_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['affiliate_id', 'user_id', 'date'], 'required'],
            [['affiliate_id', 'user_id', 'date'], 'integer'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'affiliate_id' => 'Affiliate ID',
            'user_id' => 'User ID',
            'date' => 'Date',
        ];
    }
}
