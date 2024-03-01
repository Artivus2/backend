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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            [   'label' => 'Тип счета',
                'attribute'=>'type',
                'value'=>function($model) {
                return $model->type == 1 ? 'B2B' : 'Финансовый';
                }
            ],
            [
                'label' => 'ID криптовалюты',
                'attribute' => 'chart_id',
                'value' => function($model){return $model->startChart->symbol;} 
            ],
            'start_price',
            'status',
            [   'label' => 'Выбранный споcоб вывода',
                'attribute'=>'payment_id',
                'value'=>function($model) {
                return $model->paymentType->name;
                }
            ],
            [
				'class' => 'yii\grid\ActionColumn',
				'template' => '{confirm} {reject}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				'buttons' => [

                    'confirm' => function($url, $model){
                        return Html::a('<span class="view-icon"></span>', ['view', 'id' => $model->id], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'Подтвердить вывод средств у пользователя',
                                'method' => 'post',
                            ],
                        ]);
                    },
				],
			],
            
        ],
    ]) ?>

<div>Способы оплаты Пользователя / компании</div>
<?php
   if ($model->type == 0) {
   echo GridView::widget([
       'dataProvider' => $payments,
       'tableOptions' => [
            
        'class'=>'table table-striped table-responsive'
        ],
       'columns' => [
           
           [
            'label' => 'Банк',
            
            'value' => function($data){return $data->type->name ?? null;} 
           ],
           [
            'label' => '№ карты',
            
            'value' => function($data){return $data->value ?? null;} 
           ],
           [
            'label' => 'Получатель',
            
            'value' => function($data){return $data->payment_receiver ?? null;} 
           ],
           
        ],

    ]);
} else {
    
    echo GridView::widget([
        'dataProvider' => $b2bpayments,
        'tableOptions' => [
             
         'class'=>'table table-striped table-responsive'
         ],
        'columns' => [
            
            'fio_courier',
            'phone_courier',
            'build_for_courier',
            'street_for_courier',
            'pod_for_courier',
            'description',
            'summa',
            'type',
            'value',
            'payment_receiver',
            'bank'
         ],
 
     ]);
}
 ?>



</div>
