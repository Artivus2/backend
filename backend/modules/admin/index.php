<?php

use app\models\Book;
use app\models\BookSearch;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;



$this->title = 'Мероприятия - админка';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать мероприятие', ['create'], ['class' => 'btn btn-success', 'style' => Yii::$app->user->isGuest ? 'display:none' : 'display:inline-block']) ?>
    </p>

    <?php Pjax::begin(); ?>
    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'cover_image',
                'format' => ['image',['width'=>'100','height'=>'50']],
                'value' => function($data) { return Yii::getAlias('@web').'/images/'. $data->cover_image; },
                
            ],
            
            'year',
            'description:ntext',
            'isbn',
            [
                    'class' => DataColumn::class,
                    'attribute' => 'authorName',
                    'value' => function($data) {
                        if (count($data->authors)) {
                            /** @var Author $item */
                            return implode(',',array_map(function($item){
                                return $item->full_name;
                            }, $data->authors));
                        }
                        return '';
                    }
            ],
            [
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {update} {delete}',
                'header' => 'Действия',
                'contentOptions' => ['style' => 'display: flex;'],
				
			],
            
           
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
