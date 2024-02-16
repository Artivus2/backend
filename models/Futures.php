<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "futures".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chart_id
 * @property int $wallet_id
 * @property int $type
 * @property int $order_type
 * @property int $margin_type
 * @property int $multiplier
 * @property float $price
 * @property float $limit_price
 * @property float $amount
 * @property float $final_amount
 * @property int $date
 * @property int $status
 */
class Futures extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'futures';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id', 'wallet_id', 'type', 'order_type', 'margin_type', 'multiplier', 'price', 'amount', 'date'], 'required'],
            [['user_id', 'chart_id', 'wallet_id', 'type', 'order_type', 'margin_type', 'multiplier', 'date', 'status'], 'integer'],
            [['price', 'limit_price', 'amount', 'final_amount'], 'number'],
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
            'margin_type' => 'Margin Type',
            'multiplier' => 'Multiplier',
            'price' => 'Price',
            'limit_price' => 'Limit Price',
            'amount' => 'Amount',
            'final_amount' => 'Final Amount',
            'date' => 'Date',
            'status' => 'Status',
        ];
    }
}
