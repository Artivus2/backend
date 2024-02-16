<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$this->title = 'Изменить связанный ордер: ' . $model->b2b_ads_id;
$this->params['breadcrumbs'][] = ['label' => 'B2B история', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->b2b_ads_id, 'url' => ['view', 'id' => $model->b2b_ads_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="b2b-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_history', [
        'model' => $model
        
    ]) ?>

</div>