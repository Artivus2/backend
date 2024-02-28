<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;

$this->title = 'Баланс финансового кошелька: ' . $model->email;
$this->params['breadcrumbs'][] = ['label' => ' >>>> Пользователи >>>> ', 'url' => ['index']];

?>
<div class="users-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>
    
<?php
   echo GridView::widget([
       'dataProvider' => $wallet,
       'columns' => [
           
           [
            'label' => 'Тип баланса',
            'attribute' => 'title',
            'value' => function($data){return $data->walletType->title??null;} 
           ],
           [
            'label' => 'Криптовалюта',
            'attribute' => 'symbol',
            'value' => function($data){return $data->chart->symbol;}
            ],
           [
            'label' => 'Баланс',
            'attribute' => 'balance',
            'value' => function($data){return $data->balance;}
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
                'update' => function ($url,$wallet) {
                    return Html::a(
                    '<span class="edit-icon"></span>', 
                    ['/admin/user/editbalance', 'id' => $wallet->id]);
                },
                // 'delete' => function ($url,$wallet) {
                //     return Html::a(
                //     '<span class="delete-icon"></span>', 
                //     ['/admin/user/deletebalance', 'id' => $wallet->id, 
                //     'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.']);
                // },
                'delete' => function($url, $wallet){
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
    
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>
