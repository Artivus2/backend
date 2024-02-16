<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "name"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="name", type="string")
 */
class PaymentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['name'], 'required'],
            ['currency_id', 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }
    public function getTypesname()
    {
        return $this->hasOne(PaymentUser::class, ['payment_id' => 'id']);
    }

    
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
        ];
    }
}
