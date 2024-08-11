<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var \app\models\Book $book */

$this->title = $book->title;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $book->id], [
                'class' => 'btn btn-primary',
                'style' => \Yii::$app->user->isGuest ? 'display:none' : 'display:inline-block'
        ]) ?>
        <?= Html::a('Delete', ['delete', 'id' => $book->id], [
            'class' => 'btn btn-danger',
            'style' => \Yii::$app->user->isGuest ? 'display:none' : 'display:inline-block',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php if ($book->cover_image): ?>
    <p><?= Html::img('/images/' . $book->cover_image,['width'=>'200','height'=>'100']) ?></p>
    <?php endif; ?>

    <?php
    $authors = '';
    for($i = 0; $i<count($book->authors); $i++) {
        $authors .= $book->authors[$i]->full_name;
        if ($i < count($book->authors) - 1) {
            $authors .= ', ';
        }
    }
    ?>

    <?= DetailView::widget([
        'model' => $book,
        'attributes' => [
            'id',
            'title',
            'year',
            'description:ntext',
            'isbn',
            [                                                  // name свойство зависимой модели owner
                'label' => 'Authors',
                'value' => $authors,
                'contentOptions' => ['class' => 'bg-red'],     // настройка HTML атрибутов для тега, соответсвующего value
                'captionOptions' => ['tooltip' => 'Tooltip'],  // настройка HTML атрибутов для тега, соответсвующего label
            ],
        ],
    ]) ?>

</div>
