<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$this->title = 'Изменить связанный ордер: ' . $model->p2p_ads_id;
$this->params['breadcrumbs'][] = ['label' => 'P2P история', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->p2p_ads_id, 'url' => ['view', 'id' => $model->p2p_ads_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="p2p-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_history', [
        'model' => $model
        
    ]) ?>

</div>