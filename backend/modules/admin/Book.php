<?php

namespace app\models;

use yii\web\UploadedFile;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $cover_image
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Author[] $authors
 */
class Book extends \yii\db\ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $coverImage;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'book';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'year', 'isbn'], 'required'],
            [['year'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at', 'cover_image'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['isbn'], 'string', 'max' => 13],
            [['isbn'], 'unique'],
            ['authors', 'each', 'rule' => ['integer']],

            [['cover_image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, webp', 'maxFiles' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'year' => 'Year',
            'description' => 'Description',
            'isbn' => 'Isbn',
            'cover_image' => 'Cover Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('book_author', ['book_id' => 'id']);
        //return $this->hasMany(BookAuthor::class, ['id' => 'author_id']);
    
    }

    /**
     * {@inheritdoc}
     * @return BookQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BookQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function upload()
    {
        
        
        foreach ($this->coverImage as $file) {
            $imageName = md5($file->baseName . time() . rand(1, 100)) . '.' . $file->extension;
            $file->saveAs(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $imageName);
        
    }
        return $imageName;
    }
}
