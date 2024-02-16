<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Chart */

$this->title = 'Create Chart';
$this->params['breadcrumbs'][] = ['label' => 'Charts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chart-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
