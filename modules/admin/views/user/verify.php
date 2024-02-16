<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;


$this->title = $model->id.'( '.$model->login.' )';
$this->params['breadcrumbs'][] = ['label' => 'Верификация', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="verify-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'style' => \Yii::$app->user->isGuest ? 'display:none' : 'display:inline-block'
        ]) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'style' => \Yii::$app->user->isGuest ? 'display:none' : 'display:inline-block',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>



    
    <?php
   echo GridView::widget([
       'dataProvider' => $verify,
       'columns' => [
           
        [
            'label' => '# фото',
            'attribute' => 'title',
            'value' => function($data){return $data->itemId??null;} 
           ],
           [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'contentOptions' => ['style' => 'display: flex'],
            'buttons' => [
                // 'view' => function ($url,$wallet) {
                //     return Html::a(
                //     '<span class="view-icon"></span>', 
                //     ['/admin/user/viewbalance', 'id' => $wallet->id]);
                // },
                'update' => function ($url,$model) {
                    return Html::a(
                    '<span class="edit-icon"></span>', 
                    ['/admin/user/editbalance', 'id' => $model->id]);
                },
                // 'delete' => function ($url,$wallet) {
                //     return Html::a(
                //     '<span class="delete-icon"></span>', 
                //     ['/admin/user/deletebalance', 'id' => $wallet->id, 
                //     'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.']);
                // },
                'delete' => function($url, $model){
                    return Html::a('<span class="delete-icon"></span>', ['delete', 'id' => $wallet->id], [
                        // 'class' => '',
                        'data' => [
                            'confirm' => 'Уверены что хотите удалить ?',
                            'method' => 'post',
                        ],
                    ]);
                }
                
            ],
        ],
        ]
    ])
 ?>

</div>
