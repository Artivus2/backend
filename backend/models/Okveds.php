<?php

namespace app\models;

use Yii;
/**
 * @SWG\Definition()
 *
 * @SWG\Property(property="id", type="integer", description="ID")
 * @SWG\Property(property="title", type="string", description="Наименование")
 * @SWG\Property(property="okved_id", type="string", description="ОКВЕД")
 */
class Okveds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'okveds';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['title'], 'string'],
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
            'okved_id' => 'ИД ОКВЕД',
            'active' => 'Активный',
        ];
    }

}
