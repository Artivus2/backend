<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "name", "status"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="chart_id", type="integer")
 * @SWG\Property(property="balance", type="number")
 */

class WalletInv extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'walletInv';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id', 'type'], 'required'],
            [['user_id', 'chart_id', 'type'], 'integer'],
            [['balance'], 'number'],
        ];
    }

    public function getChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'chart_id']);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'user_id' => 'User ID',
            'chart_id' => 'Chart ID',
            'balance' => 'Balance',
        ];
    }
}
