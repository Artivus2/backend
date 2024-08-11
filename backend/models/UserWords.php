<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_words".
 *
 * @property int $id
 * @property int $user_id
 * @property string $words
 */
class UserWords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_words';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'words'], 'required'],
            [['user_id'], 'integer'],
            [['words'], 'string'],
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
            'words' => 'Words',
        ];
    }
}
