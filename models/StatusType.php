<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "StatusType".
 *
 * @property int $id
 * @property int $status_id
 * @property string $title
 */
class StatusType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status_id','id'], 'required'],
            [['title'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status_id' => 'Статус',
            'title' => 'Тип статуса',
            'active' => 'Активный',
        ];
    }

    // public function getWalletType()
    // {
    //     return $this->hasOne(WalletType::class, ['id' => 'type']);
    // }
    
    // public function getUser()
    // {
    //     return $this->hasOne(Users::class, ['id' => 'user_id']);
    // }
    // public function getP2pAds()
    // {
    //     return $this->hasOne(P2pAds::class, ['id' => 'status']);
    // }

}
