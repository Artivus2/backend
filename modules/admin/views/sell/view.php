<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;


$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '  >>>> Реквизиты для вывода', 'url' => ['index']];
$this->params['breadcrumbs'][] ='   >>>>  Заявка №'. $this->title;
\yii\web\YiiAsset::register($this);
?>
 <p>
        <?= Html::a('Подтвердить вывод', ['confirm', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>
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
                'label' => 'Криптовалюта',
                'attribute' => 'chart_id',
                'value' => function($model){return $model->startChart->symbol;} 
            ],
            'start_price',
            'status',
            [   
                'label' => 'Способ оплаты',
                'attribute' => 'payment_id',
                'value' => function($model){return $model->type == 0 ? $model->paymentUser->type->name : '-';} 
            ],
            [   
                'label' => 'Номер карты',
                'attribute' => 'chart_id',
                'value' => function($model){return $model->type == 0 ? $model->paymentUser->value : '-';} 
            ],
            [   
                'label' => 'Получатель',
                'attribute' => 'recepient',
                'value' => function($model){return $model->type == 0 ? $model->paymentUser->payment_receiver :'-';} 
            ]
            
            
        ],
    ]) ?>

<div>Способы оплаты пользователя / компании</div>
<?php
   if ($model->wallet_direct_id == 10) {
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
}
if ($model->wallet_direct_id = 13 && $model->ipn_id !== null) {
    
    echo GridView::widget([
        'dataProvider' => $b2bpayments,
        'tableOptions' => [
             
         'class'=>'table table-striped table-responsive'
         ],
        'columns' => [
            'id',
            ['label' => 'Номер карты', 'value' => function($data){return $data->type == 0 ? $data->value ?? null : '-';}],
            ['label' => 'Получатель', 'value' => function($data){return $data->type == 0 ? $data->payment_receiver ?? null : '-';}],
            ['label' => 'Банк', 'value' => function($data){return $data->type == 0 ? $data->bank ?? null : '-';}],
            
         ],
 
     ]);
} else {
    echo GridView::widget([
        'dataProvider' => $b2bpayments,
        'tableOptions' => [
             
         'class'=>'table table-striped table-responsive'
         ],
        'columns' => [
            'id',
            ['label' => 'ФИО курьер', 'value' => function($data){return $data->type == 1 ? $data->fio_courier ?? null : '-';}],
            ['label' => 'Телефон курьер', 'value' => function($data){return $data->type == 1 ? $data->phone_courier ?? null : '-';}],
            ['label' => 'улица', 'value' => function($data){return $data->type == 1 ? $data->street_for_courier ?? null : '-';}],
            ['label' => '№ дома', 'value' => function($data){return $data->type == 1 ? $data->build_for_courier ?? null : '-';}],
            ['label' => 'подьезд', 'value' => function($data){return $data->type == 1 ? $data->pod_for_courier ?? null : '-';}],
            ['label' => 'примечание', 'value' => function($data){return $data->type == 1 ? $data->description ?? null : '-';}],
            ['label' => 'Сумма', 'value' => function($data){return $data->summa ?? null;}],
        ],
    ]);
}

 ?>



</div>
