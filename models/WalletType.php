<?php

namespace app\models;

use Yii;
/**
 * @SWG\Definition(required={"id", "title"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="title", type="string")
 * @SWG\Property(property="active", type="integer")
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
