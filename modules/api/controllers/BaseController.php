<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;

class BaseController extends Controller
{
    public $user = null;
    public $token = null;

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        $browser_lang = Yii::$app->request->headers->get('accept-language', "ru-RU");
        Yii::$app->language = $this->calculatei18nCode($browser_lang);

        $token = Yii::$app->request->headers->get('Authorization');
        $tokenModel = User::findIdentityByAccessToken($token);

        if ($tokenModel) {
            $this->user = $tokenModel->user;
            $this->token = $tokenModel;
        }

        return parent::beforeAction($action);
    }

    public static function calculatei18nCode ($browser_lang)
    {
        $code = substr($browser_lang, 0, 2);
        return $code;
    }

    public static function getError($model)
    {
        $errors = $model->getErrors();
        return array_shift($errors);
    }
}
