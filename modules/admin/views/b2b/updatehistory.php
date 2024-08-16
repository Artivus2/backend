<?php


use yii\helpers\Html;
use yii\widgets\Pjax;

            $this->registerJs(
                '$("document").ready(function(){
                            setInterval(function(){
                                
                                $.pjax.reload({container:"#update-chat", async:false});
                                console.log("обновление");
                            },10000);
                    });'
            );

$this->title = 'Изменить связанный ордер: ' . $model->id;

$this->params['breadcrumbs'][] = ['label' => ' b2b история ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->b2b_ads_id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="p2p-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_history', [
        'model' => $model,
       
    ]) ?>

     

<?php

Pjax::begin([
    'id' => 'update-chat',
    'timeout' => 5000, // Обновлять каждые 5 секунд
]);
echo $this->render('_form_chat', [
    'model' => $model,
    'messages' => $messages
    
]);




Pjax::end();

?>

<?= $this->render('_form_message', [
        'model' => $model,
        'send' => $send
        
    ]) ?>

</div>


