<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Users */

$this->title = $model->email;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="users-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'uid',
            'wallet.balance',
            'wallet.chart_id',
            'email:email',
            [
                'attribute'=> 'verify_status',
                'format' => 'raw',
                'value' => function($data){
                    return $data->verify_status ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>';
                }
            ],
            'first_name',
            'last_name',
            'country',
            'city',
            'affiliate_invitation_id',
            [
                'attribute'=> 'deleted',
                'content'=>function($data) {
                    return $data->deleted ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>';
                }
            ],
            [
                'attribute'=> 'banned',
                'content'=>function($data) {
                    return $data->banned ? '<span class="glyphicon glyphicon-ok"></span>' : '<span class="glyphicon glyphicon-remove"></span>';
                }
            ],
            'last_visit_time',
            'created_at',
            'delete_date',
            'comment',
        ],
    ]) ?>

</div>
