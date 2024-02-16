<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"id", "symbol", "name"})
 *
 * @SWG\Property(property="id", type="integer")
 * @SWG\Property(property="symbol", type="string")
 * @SWG\Property(property="name", type="string")
 * @SWG\Property(property="p2p", type="integer")
 * @SWG\Property(property="b2b", type="integer")
 */
class Currency extends \yii\db\ActiveRecord
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active','p2p','b2b'], 'integer'],
            [['symbol', 'name'], 'required'],
            [['image'], 'file', 'extensions' => 'png, jpg, jpeg'],
            [['symbol', 'name'], 'string', 'max' => 255],
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
            'symbol' => 'Symbol',
            'name' => 'Name',
            'p2p' => 'P2P',
            'b2b' => 'B2B',
        ];
    }

    public function upload(){
        $path = 'yii2images/' . $this->image->baseName . '.' . $this->image->extension;
        $this->image->saveAs($path);
        $this->attachImage($path);
        @unlink($path);
    }
}
