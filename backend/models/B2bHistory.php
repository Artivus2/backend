<?php

namespace app\models;

use Yii;


/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="b2b_ads_id", type="integer", description="ID ордера")
 * @SWG\Property(property="offer", type="number", description="Сумма ордера")
 */
class B2bHistory extends \yii\db\ActiveRecord
{
    public $image;

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }
    
    /**
     * {@inheritdoc}
     */

    public static function tableName()
    {
        return 'b2b_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['b2b_ads_id', 'author_id','creator_id', 'start_date', 'end_date', 'price','author_id_rs'], 'required'],
            [['b2b_ads_id', 'author_id', 'creator_id','payment_id', 'start_date','end_date', 'status','author_id_rs','chat_room_id'], 'integer'],
            [['image'], 'file', 'extensions' => 'png, jpg, pdf'],
            [['price'], 'number']
            
            
        ];
    }

    // public function beforeSave($insert)
    // {
    //     if (parent::beforeSave($insert)) {
    //         // Переводим значение поля date_end в формат integer
    //         $this->end_date = strtotime($this->end_date);
    //         return true;
    //     }
    //     return false;
    // }
    
    public function getPaymentUser()
    {
        return $this->hasOne(PaymentUser::class, ['id' => 'payment_id']);
    }
    
    public function getAds()
    {
        return $this->hasOne(B2bAds::class, ['id' => 'b2b_ads_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(Company::class, ['user_id' => 'author_id']);
    }

    public function getStatusHistory()
    {
        return $this->hasOne(StatusType::class, ['status_id' => 'status']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::class, ['user_id' => 'creator_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function getRs()
    {
        return $this->hasOne(B2bPayment::class, ['id' => 'author_id_rs']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'b2b_ads_id' => 'B2b Ads ID',
            'author_id' => 'Author ID',
            'author_id_rs' => 'Author ID RS',
            'creator_id' => 'Author ID',
            'payment_id' => 'Payment ID',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'price' => 'Price',
            'status' => 'Status',
            'image' => 'Документ',
            'chat_room_id' => 'Номер ИД чата'
        ];
    }

}
