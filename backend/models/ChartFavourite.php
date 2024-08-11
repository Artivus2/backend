<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "chart_favourite".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chart_id
 */
class ChartFavourite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'chart_favourite';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chart_id'], 'required'],
            [['user_id', 'chart_id'], 'integer'],
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
            'chart_id' => 'Chart ID',
        ];
    }
}
