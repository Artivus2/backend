<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\User;
use app\models\Wallet;
use app\models\Betting;

class BettingController extends BaseController
{
    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $betting = new Betting(["start_date" => time(), "user_id" => $this->user->id]);

        $betting->minutes = Yii::$app->request->post("minutes");
        $betting->amount = (float)Yii::$app->request->post("amount");
        $betting->type = Yii::$app->request->post("type");
        $betting->chart_id = Yii::$app->request->post("chart_id");
        $betting->end_date = strtotime("+" . $betting->minutes .  " minutes", $betting->start_date);

        $chart = Chart::findOne($betting->chart_id);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id]);
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0]);
        }
        $wallet->balance -= $betting->amount;
        if ($wallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }
        $betting->wallet_id = $wallet->id;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $chart->symbol . "USDT",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        $betting->start_price = $result->price;
        $betting->end_price = $result->price;

        if(!$betting->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения ставки"];
        }

        return ["success" => true, "message" => "Ставка успешно поставлена"];
    }

    public function actionHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $status = Yii::$app->request->post("status");
        if($status != 0) $status = [1,2,3];

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chart_id = Yii::$app->request->post("chart_id");

        $data = [];
        $betting_query = Betting::find()->joinWith(["chart"])->where(["user_id" => $this->user->id, "status" => $status, "chart_id" => $chart_id])->all();

        foreach ($betting_query as $betting) {
            $data[] = [
                "id" => $betting->id,
                "type" => $betting->type,
                "status" => $betting->status,
                "amount" => $betting->amount,
                "start_date" => date("Y-m-d H:i:s", $betting->start_date),
                "end_date" => date("Y-m-d H:i:s", $betting->start_date),
                "symbol" => isset($betting->chart) ? $betting->chart->symbol : "RUB",
            ];
        }

        return $data;
    }
}
