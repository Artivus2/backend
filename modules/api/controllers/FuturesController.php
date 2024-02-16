<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\User;
use app\models\Wallet;
use app\models\Futures;

class FuturesController extends Controller
{

    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $futures = new Futures(["date" => time(), "user_id" => $this->user->id]);

        $futures->order_type = Yii::$app->request->post("order_type");
        $futures->margin_type = Yii::$app->request->post("margin_type");
        $futures->multiplier = Yii::$app->request->post("multiplier");
        $futures->price = (float)Yii::$app->request->post("price");
        $futures->limit_price = (float)Yii::$app->request->post("limit_price", 0);
        $futures->amount = (float)Yii::$app->request->post("amount");
        $futures->type = Yii::$app->request->post("type");
        $futures->chart_id = Yii::$app->request->post("chart_id");

        $chart = Chart::findOne($futures->chart_id);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $wallet = Wallet::findOne(["user_id" => $user->id, "chart_id" => $chart->id, "type" => 2]);
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $user->id, "chart_id" => $chart->id, "balance" => 0, "type" => 2]);
        }
        $wallet->balance -= $futures->amount;
        if ($wallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }
        $futures->wallet_id = $wallet->id;

        if(!$futures->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения лота"];
        }

        return ["success" => true, "message" => "Лот успешно создан"];
    }

    public function actionHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $chart_id = Yii::$app->request->post("chart_id");
        $status = Yii::$app->request->post("status");

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];
        $futures_query = Futures::find()->where(["user_id" => $this->user->id, "status" => $status, "chart_id" => $chart_id])->all();

        foreach ($futures_query as $futures) {
            $data[] = [
                "id" => $futures->id,
                "type" => $futures->type,
                "order_type" => $futures->order_type,
                "margin_type" => $futures->margin_type,
                "multiplier" => $futures->multiplier,
                "price" => (double)$futures->amount,
                "limit_price" => (double)$futures->amount,
                "amount" => (double)$futures->amount,
                "status" => $futures->status,
            ];
        }

        return $data;
    }
}
