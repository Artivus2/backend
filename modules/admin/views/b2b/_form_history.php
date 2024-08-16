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



<div class="p2p-form-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class = "p2p-form-update">

    <div class="p2p-form-update-element">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(StatusType::find()->asArray()->all(), 'status_id', 'title'),
        'options' => ['placeholder' => 'Статус...'],
        'pluginOptions' => [
            'allowClear' => false
        ],
        ]); ?>
    </div>

    <div class="p2p-form-update-element">
    <?= $form->field($model, 'price')->textInput() ?>
    </div>
    
    <div class="p2p-form-update-element">
    <?= $form->field($model, 'author_id')->textInput() ?>
    </div>
    
    <div class="p2p-form-update-element">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

</div>



