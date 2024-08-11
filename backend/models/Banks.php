<?php

namespace app\models;

use Yii;
/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="id", type="integer", description="ID банка")
 * @SWG\Property(property="title", type="string", description="Наименование")
 * @SWG\Property(property="bik", type="string", description="БИК")
 * @SWG\Property(property="ks", type="string", description="Корр счет")
 * @SWG\Property(property="active", type="integer", description="ID банка")
 */

class Banks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['title','bik','ks'], 'string'],
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Наименование',
            'bik' => 'БИК',
            'ks' => 'Кор счет',
            'active' => 'Активный'
        ];
    }

}
