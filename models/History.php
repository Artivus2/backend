<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "type", "start_price", "end_price", "start_chart_id", "end_chart_id", "date", "status",''payment_id})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="type", type="integer")
 * @SWG\Property(property="start_price", type="number")
 * @SWG\Property(property="end_price", type="number")
 * @SWG\Property(property="start_chart_id", type="integer")
 * @SWG\Property(property="end_chart_id", type="integer")
 * @SWG\Property(property="date", type="integer")
 * @SWG\Property(property="status", type="integer")
 * @SWG\Property(property="ipn", type="string")
 */
class History extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'start_price', 'end_price', 'start_chart_id', 'end_chart_id', 'date'], 'required'],
            [['user_id', 'type', 'start_chart_id', 'end_chart_id', 'date', 'status','payment_id'], 'integer'],
            [['start_price', 'end_price'], 'number'],
        ];
    }

    public function getStartChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'start_chart_id']);
    }

    public function getEndChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'end_chart_id']);
    }

    public function getPaymentType()
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
            'type' => 'Type',
            'start_price' => 'Start Price',
            'end_price' => 'End Price',
            'start_chart_id' => 'Start Chart ID',
            'end_chart_id' => 'End Chart ID',
            'date' => 'Date',
            'status' => 'Status',
            'wallet_direct_id' => 'direction',
            'payment_id' => 'Payment_id',
            'ipn_id' => 'ipn_id',
        ];
    }
}
