<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "name", "status"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="type", type="integer")
 * @SWG\Property(property="status", type="integer")
 * @SWG\Property(property="comment", type="string")
 */

class UserVerify extends \yii\db\ActiveRecord
{
    public $image;

    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    public static function tableName()
    {
        return 'user_verify';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type'], 'required'],
            [['user_id', 'type', 'status'], 'integer'],
            [['image'], 'file', 'extensions' => 'png, jpg, jpeg'],
            [['comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Клиент',
            'login' => 'Фото',
            'type' => 'Тип',
            'status' => 'Статус',
            'comment' => 'Комментарий',
        ];
    }
}
