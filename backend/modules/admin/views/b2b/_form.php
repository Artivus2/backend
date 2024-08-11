<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\StatusType;
/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="b2b-form">

    <?php $form = ActiveForm::begin(); ?>



    <?= $form->field($model, 'status')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(StatusType::find()->asArray()->all(), 'status_id', 'title'),
    'options' => ['placeholder' => 'Статус...'],
   
    'pluginOptions' => [
        'allowClear' => false
    ],
    ]); ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'type')->widget(Select2::classname(), [
    'data' => ["1" => "Покупка", "2" => "Продажа"],
    'options' => ['placeholder' => 'Статус...'],
    'pluginOptions' => [
        'allowClear' => false
    ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
