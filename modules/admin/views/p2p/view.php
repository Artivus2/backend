<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;


/* @var $this yii\web\View */
/* @var $model app\models\p2pAds */

$this->title = $model->uuid;
$this->params['breadcrumbs'][] = ['label' => '  >>>> p2p Ордера', 'url' => ['index']];
$this->params['breadcrumbs'][] ='   >>>>  '. $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="p2p-view">

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
                'label' => 'Пользователь',
                'attribute' => 'login',
                'value' => function($data){return $data->user_id.' ('.$data->user->login . ')' ??null;} 
            ],
            [   
                'attribute'=>'type',
                'value'=>function($data) {
                return $data->type == 1 ? 'Покупка' : 'Продажа';
                }
            ],
            [
                'label' => 'ID криптовалюты',
                'attribute' => 'chart_id',
                'value' => function($data){return $data->chart_id.' ('.$data->chart->symbol . ')' ??null;} 
            ],
            [
                'label' => 'ID Валюты',
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
                    return $data->duration == 900 ? '15 минут' : '30 минут';
                }
            ],
            [
                'label' => 'Статус',
                'attribute' => 'status',
                'value' => function($data){return $data->statusType->title;}
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
            'label' => 'ID',
            
            'value' => function($data){return $data->payment_id??null;} 
           ],
           [
            'label' => 'Наименование',
            
            'value' => function($data){return $data->paymentType->name??null;} 
           ],
           [
            'label' => 'Реквизиты автора',
            
            'value' => function($data){return $data->paymentUser->value??null;} 
           ],
           [
            'label' => 'Получатель',
            
            'value' => function($data){return $data->paymentUser->payment_receiver??null;} 
           ],
        ],

    ])
 ?>
<div>История ордеров</div>
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
                },
                'chat' => function ($url,$history) {
                    return Html::a(
                    '<span class="edit-icon">Чат</span>', 
                    ['/admin/p2p/chat', 'id' => $history->id]);
                },

                
            ],
        ],
       ]
   ])
?>

</div>
