<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\modules\api\controller\ChartController;
use app\models\Chain;
use app\models\Wallet;
use app\models\PaymentUser;
use app\models\User;
use app\models\History;
use app\models\ChartChain;
use app\models\WalletAddress;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
//use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;




/**
 * Default controller for the `api` module
 */
class WalletController extends BaseController
{
    const VERIFY_STATUS = [2];
    const WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_INV = 10; // 0 -> 5
    const WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_TRADE = 20; // 0 -> 4
    const WALLET_TRANSFER_DIRECTION_FROM_INV_TO_FIN = 30; // 5-> 0
    const WALLET_TRANSFER_DIRECTION_FROM_TRADE_TO_FIN = 40; // 4-> 0 

  
    

    /**
     * @SWG\Post(
     *    path = "/wallet/buy",
     *    tags = {"Wallet"},
     *    summary = "Купить криптовалюту",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма покупки",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Ссылка на оплату",
     *      @SWG\Definition(
     *         required={"url"},
     *         @SWG\Property(
     *             property="url",
     *             type="string"
     *         )
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionBuy()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 1]);

        $history->start_chart_id = 0;
        $history->end_chart_id = Yii::$app->request->post("chart_id");
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne($history->end_chart_id);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            //CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $chart->symbol . "RUB",
            CURLOPT_URL => "https://api.coinbase.com/v2/prices/".$chart->symbol."-RUB/spot",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        $history->end_price = 1 * $history->start_price / (float)$result->data->amount;

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания ссылки"];
        }

        $currency = 'RUB';
        $merchant_id = 34188;
        $secret_word = 'USBs@3n*1N*_0M,';

        $sign = md5($merchant_id . ':' . $history->start_price . ':' . $secret_word . ':' . $currency.':' . $history->id);

        $url = 'https://pay.freekassa.ru/?';
        $url .= 'oa='.$history->start_price;
        $url .= '&o='.$history->id;
        $url .= '&currency='.$currency;
        $url .= '&s='.$sign;
        $url .= '&m='.$merchant_id;

        return ["url" => $url];
    }

    /**
     * @SWG\Post(
     *    path = "/wallet/sell",
     *    tags = {"Wallet"},
     *    summary = "Продать/вывести криптовалюту",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма продажи/вывода",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="payment_id",
     *      in="body",
     *      description="ID способа вывода",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Заявка успешно создана",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionSell()
    {
        //status 0 в обработке, 1 - выполнено, 2 - отменено
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $history = History::find()->where(['user_id' => $this->user->id, 'status' => 0, 'type' => 2])->all();
        if ($history) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "У вас уже есть не обработанные заявки на вывод"];
        }
        
        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 2]);

        $history->start_chart_id = (int)Yii::$app->request->post("chart_id");
        $history->end_chart_id = 0;
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne(['id' => $history->start_chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $payment_id = (int)Yii::$app->request->post("payment_id");
        $payments = PaymentUser::find()->where(['user_id'=>$this->user->id, 'payment_id' => $payment_id])->all();
        if (!$payments) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Указан не существующий метод вывода/продажи"];
        }
        $history->payment_id = $payment_id;
        $history->wallet_direct_id = 0;
        $history->status = 0;


        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id]);
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0, "type" => 10]);
        }
        $wallet->balance -= $history->start_price;
        $wallet->blocked += $history->start_price;
        if ($wallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно средств на балансе"];
        }


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $chart->symbol . "RUB",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));
        curl_close($curl);
        
        if (!$result->price) {
            $history->end_price = 0;
        } else {
        $history->end_price = (float)$result->price * $history->start_price / 1;
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса", $history];
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        return ["success" => true, "message" => "Запрос отправлен в обработку"];
    }

    /**
     * @SWG\Post(
     *    path = "/wallet/exchange",
     *    tags = {"Wallet"},
     *    summary = "Обмен криптовалюты на финансовом счете",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма обмена",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Обмен успешно выполнен",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionExchange()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $type = Yii::$app->request->post("type", 0);

        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 2]);

        $history->start_chart_id = Yii::$app->request->post("from_chart_id");
        $history->end_chart_id = Yii::$app->request->post("to_chart_id");
        $history->start_price = (float)Yii::$app->request->post("price");

        $fromChart = Chart::findOne($history->start_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $toChart = Chart::findOne($history->end_chart_id);
        if (!$toChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $price = 0;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $fromChart->symbol . $toChart->symbol,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if(empty($result->price)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $toChart->symbol . $fromChart->symbol,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
            ));

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if(empty($result->price)) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Котировка не найдена"];
            }

            $price = 1 / $result->price;
        } else {
            $price = $result->price;
        }

        $fromWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "type" => $type]);
        if(!$fromWallet) {
            $fromWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $history->end_price = (float)$price * $history->start_price;

        $toWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }

        return ["success" => true, "message" => "Обмен успешно выполнен"];
    }


    /**
     * @SWG\Post(
     *    path = "/wallet/exchangeInv",
     *    tags = {"Wallet"},
     *    summary = "Обмен криптовалюты на инвестиционном счете",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма обмена",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Обмен успешно выполнен",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionExchangeInv()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $type = Yii::$app->request->post("type", 0);

        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 2]);

        $history->start_chart_id = Yii::$app->request->post("from_chart_id");
        $history->end_chart_id = Yii::$app->request->post("to_chart_id");
        $history->start_price = (float)Yii::$app->request->post("price");

        $fromChart = Chart::findOne($history->start_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $toChart = Chart::findOne($history->end_chart_id);
        if (!$toChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $price = 0;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $fromChart->symbol . $toChart->symbol,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if(empty($result->price)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $toChart->symbol . $fromChart->symbol,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
            ));

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if(empty($result->price)) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Котировка не найдена"];
            }

            $price = 1 / $result->price;
        } else {
            $price = $result->price;
        }

        $fromWallet = WalletInv::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "type" => $type]);
        if(!$fromWallet) {
            $fromWallet = new WalletInv(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $history->end_price = (float)$price * $history->start_price;

        $toWallet = WalletInv::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new WalletInv(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }

        return ["success" => true, "message" => "Обмен успешно выполнен"];
    }


    /**
     * @SWG\Post(
     *    path = "/wallet/exchangeTrade",
     *    tags = {"Wallet"},
     *    summary = "Обмен криптовалюты на торговом счете",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма обмена",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Обмен успешно выполнен",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionExchangeTrade()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $type = Yii::$app->request->post("type", 0);

        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 2]);

        $history->start_chart_id = Yii::$app->request->post("from_chart_id");
        $history->end_chart_id = Yii::$app->request->post("to_chart_id");
        $history->start_price = (float)Yii::$app->request->post("price");

        $fromChart = Chart::findOne($history->start_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $toChart = Chart::findOne($history->end_chart_id);
        if (!$toChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $price = 0;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $fromChart->symbol . $toChart->symbol,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if(empty($result->price)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $toChart->symbol . $fromChart->symbol,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
            ));

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if(empty($result->price)) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Котировка не найдена"];
            }

            $price = 1 / $result->price;
        } else {
            $price = $result->price;
        }

        $fromWallet = WalletTrade::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "type" => $type]);
        if(!$fromWallet) {
            $fromWallet = new WalletTrade(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $history->end_price = (float)$price * $history->start_price;

        $toWallet = WalletTrade::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new WalletTrade(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }

        return ["success" => true, "message" => "Обмен успешно выполнен"];
    }


    /**
     * @SWG\Post(
     *    path = "/wallet/translation",
     *    tags = {"Wallet"},
     *    summary = "Перевод криптовалюты",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_type",
     *      in="body",
     *      description="ID счета",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_type",
     *      in="body",
     *      description="ID счета",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма перевода",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Перевод успешно выполнен",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionTranslation()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chart = Chart::findOne(Yii::$app->request->post("chart_id"));
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $from_type = Yii::$app->request->post("from_type", 0);
        $to_type = Yii::$app->request->post("to_type", 0);

        $price = (float)Yii::$app->request->post("price");

        $fromWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id, "type" => $from_type]);
        if(!$fromWallet) {
            $fromWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0, "type" => $from_type]);
        }
        $fromWallet->balance -= $price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $toWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id, "type" => $to_type]);
        if(!$toWallet) {
            $toWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0, "type" => $to_type]);
        }
        $toWallet->balance += $price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        return ["success" => true, "message" => "Перевод успешно выполнен"];
    }

    /**
     * @SWG\Post(
     *    path = "/wallet/history",
     *    tags = {"Wallet"},
     *    summary = "История сделок",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="status",
     *      in="body",
     *      description="Статус сделки",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "История криптовалют",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/History")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $status = Yii::$app->request->post("status");

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];
        $history_query = History::find()->joinWith(["startChart", "endChart"])->where(["user_id" => $this->user->id, "status" => $status])->all();
        //$history_query = History::find()->joinWith(["startChart", "endChart"])->where(["user_id" => $this->user->id])->orderBy("date DESC")->all();

        foreach ($history_query as $history) {
            $data[] = [
                "id" => $history->id,
                "type" => $history->type,
                "status" => $history->status,
                "date" => date("Y-m-d H:i:s", $history->date),
                "start" => [
                    "symbol" => isset($history->startChart) ? $history->startChart->symbol : "RUB",
                    "price" => $history->start_price
                ],
                "end" => [
                    "symbol" => isset($history->endChart) ? $history->endChart->symbol : "RUB",
                    "price" => $history->end_price
                ],
            ];
        }

        return $data;
    }


    /**
     * @SWG\Get(
     *    path = "/wallet/list",
     *    tags = {"Wallet"},
     *    summary = "Список активов пользователя на счетах",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="wallettype",
     *      in="path",
     *      type="integer",
     
     *      description="Тип кошелька  0 - фин, 1 - b2b, 2 - спот, 3 - марж, 4 - торговый, 5 - инв, 6 - все, 10 - вывод ",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список активов пользователя",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Wallet")
     *      ),
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        
        $wallettype = (string)Yii::$app->request->get("wallettype");
        
        if((string)!$wallettype !== "" || $wallettype == "6") {
            $wallettype = array(0,1,2,3,4,5);
        }

        
        $wallet_query_fin = Wallet::find()->joinWith("chart")
        ->where(["active" => 1, 'wallet.user_id' => $this->user->id])
        ->andWhere([">", "wallet.balance", 0])
        ->andWhere(["type" => $wallettype])
        ->all();
        
        // 0 - фин, 1 - b2b, 2 - спот, 3 - марж, 4 - торговый, 5 - инв, 6 - все, 10 - вывод

        //to do рефакторинг убрать walletInv, trade
        $data = [];

        $nodata = false;
        if (!$wallet_query_fin) {
            $data[]='нет данных';
            return $data;

        }
        $wallers = [];
        $symbols = [];
        



        foreach ($wallet_query_fin as $wallet) {
            //if((int)$wallet->chart_id == 2024) {
            $data[] = [
                "id" => $wallet->chart_id,
                "name" => $wallet->chart->name,
                "symbol" => $wallet->chart->symbol,
                "price" => (float)$this->price($wallet->chart->symbol, "USD"),
                "balance" => (float)$wallet->balance,
                "blocked" => (float)$wallet->blocked,
                "type" => $wallet->walletType->title,
                "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
            ];
            //}

        }
        
        // foreach ($wallet_query_fin as $wallet) {
        //     if($wallet->chart->symbol == "RUB") {
        //         continue;
        //     }

        //     if($wallet->chart->symbol != "USDT") {
                
        //         $symbol = $wallet->chart->symbol . "USDT";
        //         $symbols[] = $symbol;
        //         $wallers[$symbol] = $wallet;
        //     } else {
        //         $wallers["USDT"] = $wallet;
        //     }

        // }
        
        // if (!empty($symbols)) {
        //     //coinbase
        //     $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
            
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_setopt($ch, CURLOPT_HEADER, false);
        //     $res = curl_exec($ch);
        //     curl_close($ch);
        //     $result = json_decode($res);
        //     } else {
        //         $nodata = true;
        //     }

        // if(isset($wallers["USDT"])) {
        //     $wallet = $wallers["USDT"];
        //     $data[] = [
        //         "id" => $wallet->chart->id,
        //         "name" => $wallet->chart->name,
        //         "symbol" => $wallet->chart->symbol,
        //         "price" => 1,
        //         "balance" => $wallet->balance,
        //         "blocked" => $wallet->blocked ?? null,
        //         "percent" => 0,
        //         "type" => $wallet->type,
        //         "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
        //     ];
        // }
        // if (!$nodata) {
        //     foreach ($result as $item) {
        //         $wallet = $wallers[$item->symbol];
        //         $data[] = [
        //             "id" => $wallet->chart->id,
        //             "name" => $wallet->chart->name,
        //             "symbol" => $wallet->chart->symbol,
        //             "price" => $item->lastPrice,
        //             "balance" => $wallet->balance,
        //             "blocked" => $wallet->blocked,
        //             "percent" => $item->priceChangePercent,
        //             "type" => $wallet->walletType->title,
        //             "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
        //         ];

        //     }
        // }

    

        
        
        
        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/wallet/transfer",
     *    tags = {"Wallet"},
     *    summary = "Перевод между счетами",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="WALLET_TRANSFER_DIRECTION",
     *      in="body",
     *      description="ID операции (10 - перевод с фин на инв, 20 - с фин на торг, 30 - с инв на фин, 40 - с торг на фин)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="from_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price",
     *      in="body",
     *      description="Сумма перевода",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Перевод на успешно выполнен",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *    @SWG\Response(
     *      response = 403,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(ref = "#/definitions/Result")
     *    ),
     *)
     * @throws HttpException
     */
    public function actionTransfer()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $wallet_direct_id = Yii::$app->request->post("WALLET_TRANSFER_DIRECTION");
	    $direction = array(10,20,30,40);
        
	    if(!in_array($wallet_direct_id, $direction)) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не задано направление перевода"];
        }
        
	    $status = 0;
	    $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, "wallet_direct_id" => $wallet_direct_id, "status" => $status]);
        $history->start_chart_id = Yii::$app->request->post("from_chart_id");
        $history->end_chart_id = Yii::$app->request->post("to_chart_id", $history->start_chart_id);
        $history->start_price = (float)Yii::$app->request->post("price", 0);

        $fromChart = Chart::findOne($history->start_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }
	
	    if ($history->start_price <= 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сумма должна быть больше 0"];
        }

        $toChart = Chart::findOne($history->end_chart_id);
        //if ($toChart == 0) {
            //Yii::$app->response->statusCode = 400;
            //return ["success" => false, "message" => "Валюта не найдена"];
	    //    $toChart = $fromChart;
        //}
	
	    //если с ковертацией
	    $type = 0;
        if ($fromChart != $toChart) {
        $price = 0;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $fromChart->symbol . $toChart->symbol,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        if(empty($result->price)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $toChart->symbol . $fromChart->symbol,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
            ));

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if(empty($result->price)) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Котировка не найдена"];
            }

            $price = 1 / $result->price;
        } else {
            $price = $result->price;
        }
        }
        
        

        $WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_INV = 10;
        $WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_TRADE = 20;
        $WALLET_TRANSFER_DIRECTION_FROM_INV_TO_FIN = 30;
        $WALLET_TRANSFER_DIRECTION_FROM_TRADE_TO_FIN = 40;
        //направление
        if ($wallet_direct_id==$WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_INV) {
            
        $fromWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id]);
        if(!$fromWallet) {
            $fromWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if ($fromChart!=$toChart) {
        $history->end_price = (float)$price * $history->start_price;
        } else {
        $history->end_price = $history->start_price;
        }

        $toWallet = WalletInv::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new WalletInv(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;
	

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }
    }


    if ($wallet_direct_id==$WALLET_TRANSFER_DIRECTION_FROM_FIN_TO_TRADE) {
            
        $fromWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id]);
        if(!$fromWallet) {
            $fromWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if ($fromChart!=$toChart) {
        $history->end_price = (float)$price * $history->start_price;
        } else {
        $history->end_price = $history->start_price;
        }

        $toWallet = WalletTrade::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new WalletTrade(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }
    }



    
    if ($wallet_direct_id==$WALLET_TRANSFER_DIRECTION_FROM_INV_TO_FIN) {
            
        $fromWallet = WalletInv::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id]);
        if(!$fromWallet) {
            $fromWallet = new WalletInv(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if ($fromChart!=$toChart) {
        $history->end_price = (float)$price * $history->start_price;
        } else {
        $history->end_price = $history->start_price;
        }

        $toWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }
    }


    if ($wallet_direct_id==$WALLET_TRANSFER_DIRECTION_FROM_TRADE_TO_FIN) {
            
        $fromWallet = WalletTrade::findOne(["user_id" => $this->user->id, "chart_id" => $fromChart->id]);
        if(!$fromWallet) {
            $fromWallet = new WalletTrade(["user_id" => $this->user->id, "chart_id" => $fromChart->id, "balance" => 0, "type" => $type]);
        }
        $fromWallet->balance -= $history->start_price;
        if ($fromWallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно на балансе"];
        }

        if(!$fromWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if ($fromChart!=$toChart) {
        $history->end_price = (float)$price * $history->start_price;
        } else {
        $history->end_price = $history->start_price;
        }

        $toWallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $toChart->id, "type" => $type]);
        if(!$toWallet) {
            $toWallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $toChart->id, "balance" => 0, "type" => $type]);
        }
        $toWallet->balance += $history->end_price;

        if(!$toWallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }
    }



        return ["success" => true, "message" => "Перевод успешно выполнен"];
    }
    
    protected function price($chart, $currency){
        //$data = ["price" => 0];


        $curl = curl_init();
    
        curl_setopt_array($curl, array(
    
            CURLOPT_URL => "https://api.coinbase.com/v2/prices/".$chart."-".$currency."/spot",
            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));
        //}

        $result = json_decode(curl_exec($curl));
   

        curl_close($curl);
        if ($chart == 'RUB') {
            $result->data->amount = 1;
        }
        return number_format($result->data->amount, 2, '.','') ?? null;
    }
}
