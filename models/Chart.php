<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "symbol", "name", "icon"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="symbol", type="string")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="icon", type="string")
 * @SWG\Property(property="p2p", type="integer")
 * @SWG\Property(property="b2b", type="integer")
 * @SWG\Property(property="cryptomus", type="integer")
 */
class Chart extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','active', 'yield', 'priority','p2p','b2b','cryptomus'], 'integer'],
            [['symbol', 'name'], 'string', 'max' => 255],
        ];
    }

    public function getWallet()
    {
        return $this->hasOne(Wallet::class, ['chart_id' => 'id']);
    }
    
    public function getFavourite()
    {
        return $this->hasOne(ChartFavourite::class, ['chart_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'symbol' => 'Symbol',
            'name' => 'Name',
            'yield' => 'Yield',
            'priority' => 'Priority',
            'p2p' => 'P2P',
            'b2b' => 'B2B',
            'cryptomus' => 'Cryptomus'
        ];
    }
}
