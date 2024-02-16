<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Image".
 *
 * @property int $id
 * @property string $ItemId
 * @property string $filepath
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','itemId','isMain'], 'required'],

            [['filePath','modelName','UrlAlias','name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'itemId' => 'userid',
            'isMain' => 'Main',
            'filePath' => 'filepath',
        ];
    }

}
