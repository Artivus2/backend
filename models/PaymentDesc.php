<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "text"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="text", type="string")
 */
class PaymentDesc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_desc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
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
            'text' => 'Text',
        ];
    }
}
