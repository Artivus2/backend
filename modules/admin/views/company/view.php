<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Company;

/* @var $this yii\web\View */
/* @var $model app\models\Company */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Companies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="company-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'inn',
            'ogrn',
            'name',
            'address',
            [
                'label' => 'Оквед',
                'attribute' => 'main_okved',
                'value' => function($data){return $data->okved->okved_id . ' (' . $data->okved->title . ' )';},
                
            ],
            'fio',
            'phone',
            'bank',
            'bik',
            'rs',
            'ks',
        ],
    ]) ?>

</div>
