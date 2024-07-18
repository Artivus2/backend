<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;

/* @var $this yii\web\View */
/* @var $model app\models\b2bAds */

$this->title = 'ИНН '. $model['inn'];

$this->params['breadcrumbs'][] = ['label' => ' >>>> Информация по ОКВЕД', 'url' => ['index']];
$this->params['breadcrumbs'][] = '   >>>>  '.$this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="b2b-okved">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'ogrn',
            'address',
            'okved',
	    'kpp',
	    'fio',
	    'phones'
            
        ],
    ]) ?>


</div>
