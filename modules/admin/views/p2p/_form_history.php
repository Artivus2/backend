<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use janisto\timepicker\TimePicker;
use yii\helpers\ArrayHelper;
use app\models\StatusType;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="p2p-form">

    <?php $form = ActiveForm::begin(); ?>

    

    <?= $form->field($model, 'status')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(StatusType::find()->asArray()->all(), 'status_id', 'title'),
    'options' => ['placeholder' => 'Статус...'],
    'pluginOptions' => [
        'allowClear' => false
    ],
]); ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'author_id')->textInput() ?>

    <?= $form->field($model, 'end_date')->widget(TimePicker::className(), [
         'options' => [
                  'value' => Yii::$app->formatter->asDatetime($model->end_date),
         ],
         'clientOptions' => [
            'showSecond' => false,
            
        ]
         
]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
