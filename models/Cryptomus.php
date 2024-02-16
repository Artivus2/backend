<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "symbol", "network"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="symbol", type="string")
 * @SWG\Property(property="network", type="string")
 * @SWG\Property(property="active", type="integer")
 */
class Cryptomus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cryptomus';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['symbol'], 'required'],
            [['active'], 'integer'],
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
            'symbol' => 'Symbol',
            'network' => 'Network',
            'active' => 'Active',
        ];
    }
}
