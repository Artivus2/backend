<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Commission $model */

$this->title = 'Update Commission: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Commissions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="commission-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
