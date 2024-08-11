<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "spot".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chart_id
 * @property int $wallet_id
 * @property int $type
 * @property int $order_type
 * @property float $price
 * @property float $amount
 * @property float $final_amount
 * @property int $date
 * @property int $status
 */
class Spot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'spot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id', 'wallet_id', 'type', 'order_type', 'price', 'amount', 'date'], 'required'],
            [['user_id', 'chart_id', 'wallet_id', 'type', 'order_type', 'date', 'status'], 'integer'],
            [['price', 'amount', 'final_amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'chart_id' => 'Chart ID',
            'wallet_id' => 'Wallet ID',
            'type' => 'Type',
            'order_type' => 'Order Type',
            'price' => 'Price',
            'amount' => 'Amount',
            'final_amount' => 'Final Amount',
            'date' => 'Date',
            'status' => 'Status',
        ];
    }
}
