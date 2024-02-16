<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;

/* @var $this yii\web\View */
/* @var $model app\models\b2bAds */

$this->title = 'uuid '. $model->uuid;
$this->params['breadcrumbs'][] = ['label' => ' >>>> b2b Ордера', 'url' => ['index']];
$this->params['breadcrumbs'][] = '   >>>>  '.$this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="b2b-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'uuid',
            [
                'label' => 'Компания',
                'attribute' => 'company',
                'value' => function($data){return $data->company_id.' ('.$data->company->name . ')' ??null;} 
            ],
            [   
                'attribute'=>'type',
                'value'=>function($data) {
                return $data->type == 1 ? 'Покупка' : 'Продажа';
                }
            ],
            [
                'label' => 'chart_id',
                'attribute' => 'chart_id',
                'value' => function($data){return $data->chart_id.' ('.$data->chart->symbol . ')' ??null;} 
            ],
            [
                'label' => 'currency_id',
                'attribute' => 'currency_id',
                'value' => function($data){return $data->currency_id.' ('.$data->currency->name . ')' ??null;} 
            ],
            'start_amount',
            'amount',
            'min_limit',
            'max_limit',
            'course',
            [   'attribute'=>'duration',
                'value'=>function($data) {
                    return $data->duration == 272200 ? '3 дня' : 'менее 3 дней';
                }
            ],
            [
                'label' => 'Статус',
                'attribute' => 'status',
                'value' => function($data){return $data->statusType->title;}
               ],
               [
                'label' => 'Оквед',
                'attribute' => 'main_okved',
                'value' => function($data){return $data->okved->okved_id . ' (' . $data->okved->title . ' )';}
               ],
        ],
    ]) ?>

<div>Способы оплаты ордера</div>
<?php
   echo GridView::widget([
       'dataProvider' => $payments,
       'tableOptions' => [
            
        'class'=>'table table-striped table-responsive'
        ],
       'columns' => [
           
           [
            'label' => 'ФИО получателя',
            
            'value' => function($data){return $data->fio??null;} 
           ],
           [
            'label' => 'Наименование Банка',
            
            'value' => function($data){return $data->bank??null;} 
           ],
           [
            'label' => 'БИК',
            
            'value' => function($data){return $data->bik??null;} 
           ],
           [
            'label' => 'Расч счет',
            
            'value' => function($data){return $data->rs??null;} 
           ],
           [
            'label' => 'Корр счет',
            
            'value' => function($data){return $data->ks??null;} 
           ],
           [
            'label' => 'Телефон',
            
            'value' => function($data){return $data->phone??null;} 
           ],
        ],

    ])
 ?>
 
 <div>История ордера</div>
<?php
   echo GridView::widget([
       'dataProvider' => $history,
       'columns' => [
           
           [
            'label' => 'ID предложения',
            'attribute' => 'b2b_ads_id',
            'value' => function($data){return $data->id;} 
           ],
           [
            'label' => 'Криптовалюта',
            'attribute' => 'symbol',
            'value' => function($data){return $data->ads->chart->symbol;}
            ],
            [
            'label' => 'Компания',
            'attribute' => 'company',
            'value' => function($data){return $data->author_id.' ('.$data->author->name.')' ?? null;}
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
            'template' => '{update} {delete}',
            'buttons' => [
                // 'view' => function ($url,$history) {
                //     return Html::a(
                //     '<span class="glyphicon glyphicon-cog">view</span>', 
                //     ['/admin/b2b/viewhistory', 'id' => $history->id]);
                // },
                'update' => function ($url,$history) {
                    return Html::a(
                    '<span class="glyphicon glyphicon-cog">update</span>', 
                    ['/admin/b2b/updatehistory', 'id' => $history->id]);
                },
                'delete' => function ($url,$history) {
                    return Html::a(
                    '<span class="glyphicon glyphicon-cog">delete</span>', 
                    ['/admin/b2b/deletehistory', 'id' => $history->id]);
                },
                
            ],
        ],
       ]
   ])
?>

</div>
