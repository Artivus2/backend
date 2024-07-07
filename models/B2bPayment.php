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


    public function getB2bPayment()
    {
        return $this->hasOne(B2bPayment::class, ['user_id' => 'company_id', "payment_id" => 'payment_id']);
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
            'fio_courier' => 'ФИО курьера',
            'phone_courier' => 'Телефон',
            'street_for_courier' => 'улица',
            'build_for_courier' => 'номер дома',
            'pod_for_courier' => 'подьезд',
            'description' => 'описание',
            'summa' => 'Суммы',
            'type' => 'type',
            'value' => 'Номер карты',
            'payment_receiver' => 'Получатель',
            'bank' => 'Банк',
            'ks' => 'ks',
            'bik' => 'БИК',

        ];

    }
}
