<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$this->title = 'B2B Ордера';
$this->params['breadcrumbs'][] = ['label' => 'B2B', 'url' => ['index']];
$this->params['breadcrumbs'][] = ' >>>> '.$this->title;
?>
<div class="b2b-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
