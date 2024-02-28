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
 */

class PaymentUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'payment_id', 'value', 'payment_receiver', 'active'], 'required'],
            [['user_id', 'payment_id','active'], 'integer'],
            [['value', 'payment_receiver'], 'string', 'max' => 255],
        ];
    }

    public function getType()
    {
        return $this->hasOne(PaymentType::class, ['id' => 'payment_id']);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'payment_id' => 'Payment ID',
            'value' => 'Value',
            'payment_receiver' => 'Receiver',
            'active' => 'Active'
        ];
    }
}
