<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Chart;
use app\models\Currency;
use app\models\User;
use app\models\StatusType;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\P2PSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список ордеров P2P';
$this->params['breadcrumbs'][] = '   >>>>  '.$this->title;
?>
<div class="p2p-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'tableOptions' => [
            
            'class'=>'table table-striped table-responsive'
            ],
        'columns' => [
            'id',
            'uuid',
         
            [
                'label' => 'Пользователь',
                'attribute' => 'user_id',
                'value' => function($data){return $data->user_id.' ('. $data->user->login . ')';},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'user_id',    
                    'data' => ArrayHelper::map(User::find()->asArray()->all(), 'id', 'login'),
                    'options' => ['placeholder' => 'Пользователь...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])
            ],
            [
                'label' => 'Криптовалюта',
                'value' => function($data){return $data->chart->symbol??null;},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'chart_id',    
                    'data' => ArrayHelper::map(Chart::find()->where(['p2p' => 1])->asArray()->all(), 'id', 'symbol'),
                    'options' => ['placeholder' => 'Криптовалюта...'],
                    
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])
            ],
            [
                'label' => 'Валюта',
                'value' => function($data){return $data->currency->name??null;},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'currency_id',    
                    'data' => ArrayHelper::map(Currency::find()->asArray()->all(), 'id', 'name'),
                    'options' => ['placeholder' => 'Валюта...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])
            ],
            'start_amount',
            'amount',
            [   'attribute'=>'duration',
                'content'=>function($data) {
                    return $data->duration == 900 ? '<span>15 минут</span>' : '<span>30 минут</span>';
                }
            ],
            
            [   'attribute'=>'type',
                'content'=>function($data) {
                    return $data->type == 1 ? '<span>Покупка</span>' : '<span>Продажа</span>';
                }
            ],
            [
                'attribute'=>'date',
                'content'=>function($data) {
                    return date("Y-m-d H:i:s", $data->date);
                }
                
            ],
    
            [
                'label' => 'Статус',
                'value' => function($data){return $data->statusType->title??null;},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',    
                    'data' => ArrayHelper::map(StatusType::find()->asArray()->all(), 'status_id', 'title'),
                    'options' => ['placeholder' => 'Статус...', 'multiple' => false,  'autocomplete' => 'off'],
                    'pluginOptions' => [
                        'allowClear' => true,

                    ]
                ])
            ],
            
			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				'buttons' => [
					'view' => function ($url,$model) {
						return Html::a(
						'<span class="view-icon"></span>', 
						['/admin/p2p/view', 'id' => $model->id]);
					},
                    'update' => function ($url,$model) {
                        return Html::a(
                            '<span class="edit-icon"></span>', 
                            ['/admin/p2p/update', 'id' => $model->id]);
                    }
                   
                    
					
				],
			],
        ],
    ]); ?>


</div>
