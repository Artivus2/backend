<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "p2p_payment".
 *
 * @property int $id
 * @property int $p2p_ads_id
 * @property int $payment_id
 * @property int $user_id
 */
class P2pPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'p2p_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['p2p_ads_id', 'payment_id',"user_id"], 'required'],
            [['p2p_ads_id', 'payment_id',"user_id"], 'integer'],
        ];
    }

    public function getPaymentType()
    {
        return $this->hasOne(PaymentType::class, ['id' => 'payment_id']);
    }


    public function getPaymentUser()
    {
        return $this->hasOne(PaymentUser::class, ['user_id' => 'user_id', "payment_id" => 'payment_id']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'p2p_ads_id' => 'P2p Ads ID',
            'payment_id' => 'Payment ID',
            'user_id' => 'User ID',
        ];
    }
}
