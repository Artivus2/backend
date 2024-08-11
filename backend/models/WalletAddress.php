<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wallet_address".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chain_id
 * @property string $value
 */
class WalletAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallet_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chain_id', 'value'], 'required'],
            [['user_id', 'chain_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
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
            'chain_id' => 'Chain ID',
            'value' => 'Value',
        ];
    }
}
