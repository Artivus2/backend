<?php

use app\models\History;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Вывод средств';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sell-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'end_price',
            'end_chart_id',
            [
                'attribute'=>'date',
                'filter' => \janisto\timepicker\TimePicker::widget([
                    'model'=>$dataProvider,
                    'attribute'  => 'date',
                    'mode' => 'date',
                ]),
            ],
            'status',
            'payment_id',
            'wallet_direct_id',
            [
				'class' => 'yii\grid\ActionColumn',
				'template' => '{confirm} {reject}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				'buttons' => [
					// 'confirm' => function ($url,$model) {
					// 	return Html::a(
					// 	'<span class="view-icon"></span>', 
					// 	['/admin/sell/confirm', 'id' => $model->id]);
					// },
                    // 'reject' => function ($url,$model) {
                    //     return Html::a(
                    //         '<span class="delete-icon"></span>', 
                    //         ['/admin/sell/reject', 'id' => $model->id]);
                    // },
                    'confirm' => function($url, $model){
                        return Html::a('<span class="view-icon"></span>', ['confirm', 'id' => $model->id], [
                            // 'class' => '',
                            'data' => [
                                'confirm' => 'Подтвердить вывод средств у пользователя ?',
                                'method' => 'post',
                            ],
                        ]);
                    }б
                    'reject' => function($url, $model){
                        return Html::a('<span class="delete-icon"></span>', ['reject', 'id' => $model->id], [
                            // 'class' => '',
                            'data' => [
                                'confirm' => 'Подтвердить отмену вывода средств у пользователя ?',
                                'method' => 'post',
                            ],
                        ]);
                    }
                   
                    
					
				],
			],
        ],
    ]); ?>


</div>
