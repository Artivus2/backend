<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chart_chain".
 *
 * @property int $id
 * @property int $chart_id
 * @property int $chain_id
 * @property string $symbol
 * @property string $cryptomus
 */
class ChartChain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chart_chain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chart_id', 'chain_id', 'symbol'], 'required'],
            [['chart_id', 'chain_id'], 'integer'],
            [['symbol'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chart_id' => 'Chart ID',
            'chain_id' => 'Chain ID',
            'symbol' => 'Symbol',
            'cryptomus' => 'cryptomus',
        ];
    }
}
