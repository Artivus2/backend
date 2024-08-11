<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Chart;
use app\models\Currency;
use app\models\Company;
use app\models\Okveds;
use app\models\StatusType;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Список ордеров B2B';
$this->params['breadcrumbs'][] = '   >>>>  '.$this->title;
?>
<div class="b2b-index">

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
                'label' => 'Компания',
                'attribute' => 'company',
                'value' => function($data){return $data->company->name??null;},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'company_id',    
                    'data' => ArrayHelper::map(Company::find()->asArray()->all(), 'id', 'name'),
                    'options' => ['placeholder' => 'Компания...'],
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
                    'data' => ArrayHelper::map(Chart::find()->where(['b2b' => 1])->asArray()->all(), 'id', 'symbol'),
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
            'course',
            'amount',
            // [   'attribute'=>'duration',
            //     'content'=>function($data) {
            //         return '<span>3 дня</span>';
            //     }
            // ],
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
                'label' => 'Оквед',
                'attribute' => 'main_okved',
                'value' => function($data){return $data->okved->okved_id . ' (' . $data->okved->title . ' )';},
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'main_okved',    
                    'data' => ArrayHelper::map(Okveds::find()->asArray()->all(), 'id', 'title'),
                    'options' => ['placeholder' => 'оквед...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ])
            ],
            
			[
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {update} {delete} {wallet}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				'buttons' => [
					'view' => function ($url,$model) {
						return Html::a(
						'<span class="view-icon"></span>', 
						['/admin/b2b/view', 'id' => $model->id]);
					},
                    'update' => function ($url,$model) {
                        return Html::a(
                            '<span class="edit-icon"></span>', 
                            ['/admin/b2b/update', 'id' => $model->id]);
                    },
                    'wallet' => function ($url,$model) {
                        return Html::a(
                            '<span class="wallet-icon"></span>', 
                            ['/admin/b2b/find', 'id' => $model->id]);
                    }
                   
                    
					
				],
			],
        ],
    ]); ?>


</div>
