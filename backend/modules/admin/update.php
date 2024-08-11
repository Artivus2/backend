<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\Book $book */
/** @var \app\models\Author[] $authors */

$this->title = 'Update Book: ' . $book->title;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $book->title, 'url' => ['view', 'id' => $book->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="book-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'book' => $book,
        'authors' => $authors,
    ]) ?>

</div>
