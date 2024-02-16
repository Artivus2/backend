<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Author;

/** @var yii\web\View $this */
/** @var \app\models\Book $book */
/** @var \app\models\Author[] $authors */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'name' => 'book_form']]); ?>

    <?= $form->field($book, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($book, 'year')->textInput() ?>

    <?= $form->field($book, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($book, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($book, 'authors')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(Author::find()->asArray()->all(), 'id', 'full_name'),
    'options' => ['placeholder' => 'Авторы...','multiple' => true],
    'pluginOptions' => [
        'allowClear' => false
    ],
]); ?>


    <?= $form->field($book, 'cover_image')->fileInput() ?>

    <div class="form-group">
        <?= Html::button('Save', ['class' => 'btn btn-success', 'onclick' => "$('[name=book_form]').attr('action','" . ($book->isNewRecord ? "/book/create" : "/book/update?id=" . $book->id) . "');$('[name=book_form]').submit();"]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
