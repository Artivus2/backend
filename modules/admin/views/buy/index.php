<?php

use app\models\History;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'История пополнений';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="buy-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => [
            
            'class'=>'table table-striped table-responsive'
            ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'start_price',
            [
                'label' => 'Криптовалюта',
                'attribute' => 'chart_id',
                'value' => function($model){return $model->start_chart_id == 0 ? "RUB" : $model->startChart->symbol;} 
            ],
            [
                'label' => 'Дата',
                'attribute' => 'status',
                'value' => function($data){return date("Y-m-d H:i:s", $data->date);}
               ],
               [
                'label' => 'Статус',
                'attribute' => 'status',
                'value' => function($model){return $model->status == -1 ? "в архиве" : $model->status;} 
            ],
            'ipn_id',
            // [
			// 	'class' => 'yii\grid\ActionColumn',
			// 	'template' => '{confirm} {reject}',
            //     'header' => 'Действия',
            //     'contentOptions' => ['style' => 'display: flex;'],
				// 'buttons' => [

                //     'confirm' => function($url, $model){
                //         return Html::a('<span class="view-icon"></span>', ['view', 'id' => $model->id], [
                //             // 'class' => '',
                //             // 'data' => [
                //             //     'confirm' => 'Подтвердить вывод средств у пользователя ?',
                //             //     'method' => 'post',
                //             // ],
                //         ]);
                //     },
                //     'reject' => function($url, $model){
                //         return Html::a('<span class="delete-icon"></span>', ['reject', 'id' => $model->id], [
                //             // 'class' => '',
                //             'data' => [
                //                 'confirm' => 'Подтвердить отмену вывода средств у пользователя, средства вернутся на баланс !!!',
                //                 'method' => 'post',
                //             ],
                //         ]);
                //     }
                   
                    
					
				// ],
			// ],
        ],
    ]); ?>


</div>
