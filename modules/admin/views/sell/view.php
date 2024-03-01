<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;

/* @var $this yii\web\View */
/* @var $model app\models\p2pAds */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '  >>>> Реквизиты для вывода', 'url' => ['index']];
$this->params['breadcrumbs'][] ='   >>>>  Заявка №'. $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="sell-view">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            [   'label' => 'Тип счета',
                'attribute'=>'type',
                'value'=>function($model) {
                return $data->type == 1 ? 'B2B' : 'Финансовый';
                }
            ],
            [
                'label' => 'ID криптовалюты',
                'attribute' => 'chart_id',
                'value' => function($model){return $model->start_chart_id;} 
            ],
            'start_price',
            'status'
        ],
    ]) ?>



</div>
