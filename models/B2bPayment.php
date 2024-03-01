<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "name"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="payment_id", type="integer")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="value", type="string")
 * @SWG\Property(property="payment_receiver", type="string")
 * @SWG\Property(property="fio_courier", type="string")
 * @SWG\Property(property="phone_courier", type="string")
 * @SWG\Property(property="street_for_courier", type="string")
 * @SWG\Property(property="build_for_courier", type="string")
 * @SWG\Property(property="pod_for_courier", type="string")
 * @SWG\Property(property="active", type="integer")
 * @SWG\Property(property="description", type="string")
 * @SWG\Property(property="summa", type="number")
 * @SWG\Property(property="type", type="integer")
 * @SWG\Property(property="bank", type="string")
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
            [['payment_id',"company_id",'type'], 'integer'],

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
            'summa' => 'summa',
            'type' => 'type',
            'value' => 'value',
            'payment_receiver' => 'payment_receiver',
            'bank' => 'bank',

        ];

    }
}
