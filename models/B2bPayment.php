<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "b2b_payment".
 *
 * @property int $id
 * @property int $b2b
 * @property int $payment_id
 * @property int $user_id
 */
class B2bPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'b2b_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [["company_id"], 'required'],
            [['payment_id',"company_id"], 'integer'],

        ];
    }

    public function getPaymentType()
    {
        return $this->hasOne(PaymentType::class, ['id' => 'payment_id']);
    }


    public function getPaymentCompany()
    {
        return $this->hasOne(PaymentUser::class, ['user_id' => 'company_id', "payment_id" => 'payment_id']);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_id' => 'Payment ID',
            'company_id' => 'Company ID',
            'fio_courier' => 'fio courier',
            'phone_courier' => 'phone courier',
            'street_for_courier' => 'street courier',
            'build_for_courier' => 'build courier',
            'pod_for_courier' => 'pod courier',
            'description' => 'description',
        ];

    }
}
