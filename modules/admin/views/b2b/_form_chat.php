<?php

use yii\helpers\Html;

use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\StatusType;

$chat = 'Чат по ордеру: ' . $model->id;

?>

<div class="chat-title">
        <?= Html::encode($chat) ?>
    </div>


    <div class="chat">
    <div class="chat-content" id="update-chat">

    <?php foreach ($messages as $item) {

        if ($model->author_id == $item["sender_user_id"]) {
            ?>  
        
            <div class="chat-content-author-user">
    
                <div class="chat-user-author">
                <?=Html::encode($item["login"]) ?>
                </div>
                <div class="chat-message">
                <?= Html::encode($item["primary_message"]) ?>
                </div>

                
                <?php if (isset($item["attachment"])) {
                    ?> 
                    <div class="chat-image">
                    <img src="\yii2images\<?=$item['attachment']?>"/>
                        </div>
                <?php }?>
                

                <div class="chat-date">
                <?= Html::encode($item["date_time"]) ?>
                </div>
            </div>
                
            <?php }
        if ($model->creator_id == $item["sender_user_id"]) {
            ?>  
        
            <div class="chat-content-creator-user">
    
                <div class="chat-user-creator">
                <?=Html::encode($item["login"]) ?>
                </div>
                <div class="chat-message">
                <?= Html::encode($item["primary_message"]) ?>
                </div>

                <?php if (isset($item["attachment"])) {
                    ?> 
                    <div class="chat-image">
                    <img src="\yii2images\<?=$item['attachment']?>"/>
                        </div>
                <?php }?>

                <div class="chat-date">
                <?= Html::encode($item["date_time"]) ?>
                </div>
            </div>
                
            <?php }
        if ($item["sender_user_id"] == Yii::$app->params['chat_admin']) {
            ?>  
        
            <div class="chat-content-admin">
    
                <div class="chat-admin">
                <?=Html::encode($item["login"]) ?>
                </div>
                <div class="chat-message">
                <?= Html::encode($item["primary_message"]) ?>
                </div>

                <?php if (isset($item["attachment"])) {
                    ?> 
                    <div class="chat-image">
                        <img src="\yii2images\<?=$item['attachment']?>"/>
                        </div>
                <?php }?>

                <div class="chat-date">
                <?= Html::encode($item["date_time"]) ?>
                </div>
            </div>
        
        <?php }
    
    }?>
        

        </div>
    </div>
        


