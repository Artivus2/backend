<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "betting".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chart_id
 * @property int $wallet_id
 * @property int $type
 * @property float $amount
 * @property float $start_price
 * @property float $end_price
 * @property int $minutes
 * @property int $start_date
 * @property int $end_date
 * @property int $status
 */
class Betting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'betting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id', 'wallet_id', 'type', 'amount', 'start_price', 'end_price', 'minutes', 'start_date', 'end_date'], 'required'],
            [['user_id', 'chart_id', 'wallet_id', 'type', 'minutes', 'start_date', 'end_date', 'status'], 'integer'],
            [['amount', 'start_price', 'end_price'], 'number'],
        ];
    }

    public function getChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'chart_id']);
    }

    public function getWallet()
    {
        return $this->hasOne(Wallet::class, ['id' => 'wallet_id']);
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
            'amount' => 'Amount',
            'start_price' => 'Start Price',
            'end_price' => 'End Price',
            'minutes' => 'Minutes',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
        ];
    }
}
