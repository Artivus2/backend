<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Chart;
use app\models\Currency;
use app\models\Company;
use app\models\Okveds;
use app\models\StatusType;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ' Компании ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'inn',
            'ogrn',
            'name',
            'address',
            [
                'label' => 'Оквед',
                'attribute' => 'main_okved',
                'value' => function($data){return $data->okved->okved_id??null . ' (' . $data->okved->title??null . ' )';},
                
            ],
            'fio',
            'phone',
            
            [
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				'buttons' => [
					'view' => function ($url,$model) {
						return Html::a(
						'<span class="view-icon"></span>', 
						['/admin/company/view', 'id' => $model->id]);
					},
                    'update' => function ($url,$model) {
                        return Html::a(
                            '<span class="edit-icon"></span>', 
                            ['/admin/company/update', 'id' => $model->id]);
                    },
                    'wallet' => function ($url,$model) {
                        return Html::a(
                            '<span class="delete-icon"></span>', 
                            ['/admin/company/delete', 'id' => $model->id]);
                    }
                   
                    
					
				],
			],
            
        ],
    ]); ?>


</div>
