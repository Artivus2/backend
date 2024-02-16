<?php

namespace app\commands;

use yii\console\Controller;
use app\Models\User;

class TokenController extends Controller
{
    public $id;

    public function options($actionID)
    {
        return ['id'];
    }

    public function optionAliases()
    {
        return ['id' => 'id'];
    }

    public function actionIndex()
    {
        $user = User::findOne(['id' => $this->id]);
        $token = $user->setToken('fcm_test_console');
        if ($token) {
            echo 'Успешно, токен: ' . $token->token;
        } else {
            echo 'Ошибка';
        }
    }
}
