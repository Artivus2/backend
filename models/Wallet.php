<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "name", "status"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="symbol", type="string")
 * @SWG\Property(property="price", type="number")
 * @SWG\Property(property="balance", type="number")
 * @SWG\Property(property="percent", type="number")
 * @SWG\Property(property="type", type="integer")
 * @SWG\Property(property="icon", type="string")
 * @SWG\Property(property="blocked", type="number")
 */

class Wallet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallet';
    }


    public function rules()
    {
        return [
            [['user_id','chart_id','type'], 'required'],
            [['user_id'], 'integer'],
            [['balance','blocked'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'balance' => 'Баланс',
            'blocked' => 'Заморожено',
            'chart_id' => 'ИД криптовалюты',
            'type' => 'Тип кошелька',
        ];
    }


    public function getChart()
    {
        return $this->hasOne(Chart::class, ['id' => 'chart_id']);
    }
    public function getWalletType()
    {
        return $this->hasOne(WalletType::class, ['id' => 'type']);
    }
    
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }
    /**
     * {@inheritdoc}
     */
   
}
