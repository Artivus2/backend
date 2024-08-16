<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;


/* @var $this yii\web\View */
/* @var $model app\models\p2pAds */

$this->title = 'Спорные сделки';

\yii\web\YiiAsset::register($this);
?>
<div class="p2p-view">

    <h1><?= Html::encode($this->title) ?></h1>

 <div>Спорные сделки</div>
<?php
   echo GridView::widget([
       'dataProvider' => $history,
       'tableOptions' => [
            
        'class'=>'table table-striped table-responsive'
        ],
       'columns' => [
           [
            'label' => 'ID предложения',
            'attribute' => 'p2p_ads_id',
            'value' => function($data){return $data->id;} 
           ],
           [
            'label' => 'Криптовалюта',
            'attribute' => 'symbol',
            'value' => function($data){return $data->ads->chart->symbol;}
            ],
            [
            'label' => 'Автор предложения',
            'attribute' => 'author_id',
            'value' => function($data){return $data->author_id.' ('.$data->author->login.')' ?? null;}
            ],
            [
            'label' => 'Обьем ордера',
            'attribute' => 'price',
            'value' => function($data){return $data->price;}
           ],
           [
            'label' => 'Статус',
            'attribute' => 'status',
            'value' => function($data){return $data->statusHistory->title;}
           ],
           [
            'label' => 'Начало срока',
            'attribute' => 'status',
            'value' => function($data){return date("Y-m-d H:i:s", $data->start_date);}
           ],
           [
            'label' => 'Окончание срока',
            'attribute' => 'status',
            'value' => function($data){return date("Y-m-d H:i:s", $data->end_date);}
           ],
           [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{chat} {update} {delete}',
            'contentOptions' => ['style' => 'display: flex'],
            'buttons' => [
                
                'update' => function ($url,$history) {
                    return Html::a(
                    '<span class="view-icon"></span>', 
                    ['/admin/p2p/updatehistory', 'id' => $history->id]);
                },
                'delete' => function($url, $history){
                    return Html::a('<span class="delete-icon"></span>', ['deletehistory', 'id' => $history->id], [
                        // 'class' => '',
                        'data' => [
                            'confirm' => 'Уверены что хотите удалить ?',
                            'method' => 'post',
                        ],
                    ]);
                }
                

                
            ],
        ],
       ]
   ])
?>

</div>
