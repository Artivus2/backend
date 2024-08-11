<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "symbol", "name"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="symbol", type="string")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="cryptomus", type="integer")
 */
class Chain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['active'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'cryptomus' => 'Cryptomus',
        ];
    }
}
