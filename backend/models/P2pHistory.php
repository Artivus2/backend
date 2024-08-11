<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="p2p_ads_id", type="integer", description="ID ордера")
 * @SWG\Property(property="offer", type="number", description="Сумма ордера")
 * @SWG\Property(property="payment", type="integer", description="способ оплаты")
 */

class P2pHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'p2p_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['p2p_ads_id', 'creator_id', 'author_id', 'start_date', 'end_date', 'price'], 'required'],
            [['p2p_ads_id', 'creator_id', 'author_id', 'start_date','end_date', 'status', 'payment_id','chat_room_id'], 'integer'],
            [['price'], 'number']
            
            
        ];
    }

    // public function beforeSave($insert)
    // {
    //     if (parent::beforeSave($insert)) {
    //         // Переводим значение поля date_end в формат integer
    //         if ($this->user->is_admin == 1) {
    //         $this->end_date = strtotime($this->end_date);
    //         }
    //         return true;
    //     }
    //     return false;
    // }

    // public function afterFind(){
    //     $this->end_date = \Yii::t('app', Yii::$app->formatter->asDate($this->end_date));
    //     $this->end_date = date('Y-m-d H:i', $this->end_date);
    //     return parent::afterFind();
    // }

    public function getPaymentUser()
    {
        return $this->hasOne(PaymentUser::class, ['id' => 'payment_id']);
    }

    public function getAds()
    {
        return $this->hasOne(P2pAds::class, ['id' => 'p2p_ads_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
    
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'creator_id']);
    }

    public function getStatusHistory()
    {
        return $this->hasOne(StatusType::class, ['status_id' => 'status']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'p2p_ads_id' => 'P2p Ads ID',
            'creator_id' => 'Creator ID',
            'author_id' => 'Author offer ID',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'payment_id' => 'Payment Id',
            'price' => 'Price',
            'status' => 'Status',
            'chat_room_id' => 'ИД чата'
        ];
    }
}
