<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \app\models\Book $book */
/** @var \app\models\Author[] $authors */

$this->title = 'Create Book';
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'book' => $book,
        'authors' => $authors,
    ]) ?>

</div>
