<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Chart;
use app\models\WalletType;

/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'balance')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'chart_id')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(Chart::find()->asArray()->all(), 'id', 'symbol'),
    'options' => ['placeholder' => 'Криптовалюта...'],
    'pluginOptions' => [
        'allowClear' => false
    ],
]); ?>

<?= $form->field($model, 'type')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(WalletType::find()->asArray()->all(), 'id', 'title'),
    'options' => ['placeholder' => 'Тип фин. баланса...'],
    'pluginOptions' => [
        'allowClear' => false
    ],
]); ?>
 

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
