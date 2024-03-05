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
class PaymentStatusType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','type'], 'required'],
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
            'title' => 'Наименование',
            'type' => 'Тип статуса',
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
