<?php

namespace app\models;

use Yii;

/**
 * @SWG\Definition(required={"user_id", "type", "user_id_rater"})
 * @SWG\Property(property="user_id", type="integer")
 * @SWG\Property(property="type", type="integer")
 * @SWG\Property(property="description", type="string")
 * @SWG\Property(property="user_id_rater", type="integer")
 */
class RatingsHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ratings_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type'], 'required'],
            [['user_id'], 'integer'],
            [['type'], 'integer'],
            [['description'], 'string'],
            [['description'], 'string', 'max' => 255],
            [['user_id_rater'], 'integer'],
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
            'type' => 'Type',
            'created_at' => 'Date',
            'description' => 'Description',
            'user_id_rater' => 'ID who gave rating',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id_rater']);
    }
}
