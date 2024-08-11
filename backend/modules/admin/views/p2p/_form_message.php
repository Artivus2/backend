<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

$form_chat = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
<div class="chat-message-send">
    <div class="chat-upload">
        <!-- <span class="uploads-icon"></span> -->
        <!-- <?= $form_chat->field($send, 'attachment')->fileInput() ?> -->
        
        
    </div>
    
    <div class="chat-text">
        <?= $form_chat->field($send, 'primary_message')->textInput(['class' =>'chat-text-input'])->label(false)  ?>
    </div>

    <div class="chat-send">
        <?= Html::submitButton('', ['class' => 'send-icon send-icon-button']) ?>
        
    </div>
</div>
<?php ActiveForm::end(); ?>

<script>
// $('#form-left').on('beforeSubmit', function(){ ... });
</script>