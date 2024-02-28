<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\UserVerify */

$this->title = 'Create User Verify';
$this->params['breadcrumbs'][] = ['label' => 'User Verifies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-verify-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
