<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Okveds;

/* @var $this yii\web\View */
/* @var $model app\models\Company */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-form">

    <?php $form = ActiveForm::begin(); ?>

    
    <?= $form->field($model, 'inn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ogrn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kpp')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bank')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bik')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rs')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'ks')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'main_okved')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(Okveds::find()->asArray()->all(), 'id', 'okved_id','title'),
    'options' => ['placeholder' => 'Статус...'],
   
    'pluginOptions' => [
        'allowClear' => false
    ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
