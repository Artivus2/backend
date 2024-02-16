<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wallet_type".
 *
 * @property int $id
 * @property int $title
 * @property float $active
 */
class WalletType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallet_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','active'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'active' => 'Активный',
        ];
    }

    public function getWallet()
    {
        return $this->hasOne(Wallet::class, ['id' => 'type']);
    }

    
}
