<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = '   >>>>  '.$this->title;
?>
<div class="users-index">

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
            'uid',
            //'app_id',
            'email:email',
            //'token',
            [
                'attribute'=> 'verify_status',
                // 'filter' => [ "0"=> "Нет", "1"=>"Да"],
                'content'=>function($data) {
                    return $data->verify_status ? 'Пройдена' : 'Не пройдена';
                }
            ],
            //'telegram',
            'first_name',
            'last_name',
            'country',
            // [
            //     'attribute'=>'dollars',
            //     'label' => 'Счет',
            //     'content'=>function($data){
            //         return $data->wallet->dollars;
            //     }
            // ],
            // [
            //     'attribute'=>'virtual_dollars',
            //     'label' => 'Демо-счет',
            //     'content'=>function($data){
            //         return $data->wallet->virtual_dollars;
            //     }
            // ],
            //'city',
            //'password',
            //'is_admin',
            //'affiliate_invitation_id',
            //'deleted',
            //'banned',
            //'last_visit_time',
            [
                'attribute'=>'created_at',
                'filter' => \janisto\timepicker\TimePicker::widget([
                    'model'=>$searchModel,
                    'attribute'  => 'created_at',
                    'mode' => 'date',
                ]),
            ],

            
			[   
				'class' => 'yii\grid\ActionColumn',
				'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex'],
                'template' => '{view} {wallet} {update} {delete} ',
				'buttons' => [
					
                    'wallet' => function ($url,$model) {
                        return Html::a(
                            '<span class="wallet-icon"></span>',
                            $url);
                    },
					// 'password' => function ($url,$model,$key) {
					// 	return Html::a(
					// 	'<span class="password-icon"></span>', 
					// 	$url);
					// },
				],
			],
        ],
    ]); ?>


</div>
