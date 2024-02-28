<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\Currency;
use app\models\Chain;
use app\models\Wallet;
use app\models\WalletType;
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
use CoinRemitter\CoinRemitter;




/**
 * Default controller for the `api` module
 */
class WalletController extends BaseController
{
    const VERIFY_STATUS = [0,1,2];
    const COMISSION_IN = 0; //0% КОМИССИЯ
    const COMISSION_OUT = 0.1; //0.1% КОМИССИЯ
    //to do комисся в настройках админа

  
    

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

        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 11, 'status' => 0]);

        $history->start_chart_id = 0;
        $history->end_chart_id = Yii::$app->request->post("chart_id");
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne($history->end_chart_id);


        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        

        $history->end_price = $history->start_price / (float)$this->price($chart->symbol, "RUB") - $history->start_price / (float)$this->price($chart->symbol, "RUB") * self::COMISSION_IN / 100;

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
     *    path = "/wallet/input",
     *    tags = {"Wallet"},
     *    summary = "Пополнить/внести криптовалюту",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="amount",
     *      in="body",
     *      description="Сумма ввода/пополнения",
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
    public function actionInput()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $history = History::find()->where(["user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0])->all();
        if ($history) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Завершите предыдущие заявки на пополнение или обратитесь к технической поддержке"];
        } else {
            $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0]);
        }

        
        $history->end_chart_id = Yii::$app->request->post("chart_id");
        $history->start_chart_id = $history->end_chart_id;
        
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = chart::findOne($history->end_chart_id);
        $history->end_price = 0;
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }


        $params = [
            'coin'=>$chart->symbol, //coin for which you want to use this object.
            'api_key'=>'$2y$10$UK8VoHoh/kTDP2u0XW6TDOCYWx87cF0eRmZRyuG35FmsrDgSKkqRy', //api key from coinremitter wallet
            'password'=>'12345678' //password for selected wallet
         ];
        $obj = new CoinRemitter($params);

        $amount = $history->end_chart_id;

         $param = [
            'amount'=>$history->start_price, //required.
            'notify_url'=>'https://greenavi.com/api/payment/notice-ipn', //required,you will receive notification on this url,
            'fail_url'=>'https://greenavi.com/api/payment/fail-ipn', //required,you will receive notification on this url,
            'success_url'=>'https://greenavi.com/api/payment/success-ipn', //required,you will receive notification on this url,
            'name'=>'i' .rand(100000000,999999999),//optional,
            //'currency'=>"RUB",//optional,
            'expire_time'=>10,//in minutes,optional,
            'description'=>'test',//optional,
        ];
        
        $invoice  = $obj->create_invoice($param);
        $history->ipn_id = $invoice["data"]["invoice_id"];
        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания ссылки"];
        }
        
        return $invoice;

    }

    // public function actionGetBalance() {
        
    //     Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    //     // $params = [
    //     //     'coin'=>'BTC', //coin for which you want to use this object.
    //     //     'api_key'=>'$2y$10$mz1G3jtfe2ZbfiJlRvkDau6Zmwhf5R5eq4Tfxvs5ofZeHzgKG8n.y', //api key from coinremitter wallet
    //     //     'password'=>'12345678' //password for selected wallet
    //     //  ];
    //     //  $obj = new CoinRemitter($params);

    //     //  //$balance = $obj->get_balance();
    //     //  $address = $obj->get_new_address();
    //     $params = [
    //         'coin'=>'TCN', //coin for which you want to use this object.
    //         'api_key'=>'$2y$10$UK8VoHoh/kTDP2u0XW6TDOCYWx87cF0eRmZRyuG35FmsrDgSKkqRy', //api key from coinremitter wallet
    //         'password'=>'12345678' //password for selected wallet
    //      ];
    //     $obj = new CoinRemitter($params);
    //     $balance = $obj->get_balance();
        
    //     return $balance;
    // }

    

    

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
     *      description="ID способа вывода (курьер ид 2000)",
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
        
        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }

        $history = History::find()->where(['user_id' => $this->user->id, 'status' => 0, 'type' => 0])->all();
        if ($history) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "У вас уже есть не обработанные заявки на вывод"];
        }
        
        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 10]);

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
        
        $history->status = 0;


        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id,'type' => 0]); //фин
        if(!$wallet) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Счет не найден"];
        }
        $wallet->balance -= $history->start_price + $history->start_price * self::COMISSION_OUT / 100;
        $wallet->blocked += $history->start_price + $history->start_price * self::COMISSION_OUT / 100;
        if ($wallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно средств на балансе"];
        }


        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     //CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $chart->symbol . "RUB",
        //     CURLOPT_URL => "https://api.coinbase.com/v2/prices/".$chart->symbol."-RUB/spot",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        //     CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        // ));

        // $result = json_decode(curl_exec($curl));
        // curl_close($curl);
        
        if (!(float)$this->price($chart->symbol, "RUB")) {
            $history->end_price = 0;
        } else {
        $history->end_price = (float)$this->price($chart->symbol, "RUB") * $history->start_price / 1;
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
     
     *      description="Тип кошелька  0 - фин, 1 - b2b, 2 - спот, 3 - марж, 4 - торговый, 5 - инв, 6 - все (кроме b2b), 10 - вывод ",
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
            $wallettype = array(0,2,3,4,5);
        }

        
        $wallet_query_fin = Wallet::find()->joinWith("chart")
        ->where(['wallet.user_id' => $this->user->id])
        ->andWhere([">", "wallet.balance", 0])
        ->andWhere(["type" => $wallettype])
        ->all();
        
        // 0 - фин, 1 - b2b, 2 - спот, 3 - марж, 4 - торговый, 5 - инв, 6 - все, 10 - вывод


        $data = [];

        if (!$wallet_query_fin) {
            $data[]='нет данных';
            return $data;

        }

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
        }
        
         
        return $data;
    }
    
    /**
     * @SWG\Post(
     *    path = "/wallet/transfer",
     *    tags = {"Wallet"},
     *    summary = "Перевод между счетами",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="from_wallet_id",
     *      in="body",
     *      description="ID кошелька (0	Финансовый, 1	B2B,   2	Спотовый,    3	Маржинальный,    4	Торговый,    5	Инвестиционный)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="to_wallet_id",
     *      in="body",
     *      description="ID кошелька (0	Финансовый, 1	B2B,   2	Спотовый,    3	Маржинальный,    4	Торговый,    5	Инвестиционный)",
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
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="summa",
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
        
        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }
        
        
        $from_wallet_id = (int)Yii::$app->request->post("from_wallet_id");
        
        $to_wallet_id = (int)Yii::$app->request->post("to_wallet_id");
        
        if ((int)$from_wallet_id == 1) {
            if ((int)$to_wallet_id !== 0) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "перевод с b2b только на финансовый кошелек"];
            }
        }

        if ($from_wallet_id == $to_wallet_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не корректное ИД кошелька"];
        }

        $from_chart_id = Yii::$app->request->post("from_chart_id");
        $to_chart_id = Yii::$app->request->post("to_chart_id");

        
        if (!$from_chart_id || !$to_chart_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не корректное ИД криптовалют"];
        }

        $convert = true;
        if ($to_chart_id == $from_chart_id) {
            $convert = false;
        }


        
        $summa = Yii::$app->request->post("summa");
        if (!$summa || $summa < 0) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не корректное сумма"];
        }
        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => $to_wallet_id, "wallet_direct_id" => $from_wallet_id, "status" => 0]);
        $history->start_chart_id = $from_chart_id;
        $history->end_chart_id = $to_chart_id;
        $fromChart = Chart::findOne($history->start_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }
        $toChart = Chart::findOne($history->end_chart_id);
        if (!$fromChart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }
        $from_wallet = Wallet::findOne(['user_id' => $history->user_id, 'type' => $history->wallet_direct_id, 'chart_id' => $history->start_chart_id]);
        $to_wallet = Wallet::findOne(['user_id' => $history->user_id, 'type' => $history->type, 'chart_id' => $history->end_chart_id]);
        if (!$from_wallet) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Счет не найден"];
        }

        if (!$to_wallet) {
            $to_wallet = new Wallet(['user_id' => $this->user->id, 'type' => $history->type, 'chart_id' => $history->end_chart_id, 'balance' => 0, 'blocked' => null]);
        }
        
        $history->start_price = $summa;

        $history->end_price = $summa * (float)$this->price($fromChart->symbol, $toChart->symbol);

        $from_wallet->balance -= (float)$summa;

        $to_wallet->balance += $history->end_price;
        
            
        if ($from_wallet->balance < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Недостаточно средств на балансе"];
        }


        $history->status = 1;
        
        if (!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка перевода", $history];   
        }

        if (!$from_wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета исходящего"];   
        }

        if (!$to_wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета входящего"];   
        }

        
        return ["success" => true, "message" => "Перевод успешно выполнен"];
    }
    
    /**
     * @SWG\Get(
     *    path = "/Wallet/types",
     *    tags = {"Wallet"},
     *    summary = "Список Счетов",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список Счетов",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/WalletType")
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

   
     public function actionTypes()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }

        $result = WalletType::find(['active' => 1])->all();

        

        return $result;
     }
    
    
     protected function price($chart1, $chart2){
        
        if ($chart1 == "TCN") {
            $chart1 = "USDT";
        }
        if ($chart2 == "TCN") {
            $chart2 = "USDT";
        }

        $curl = curl_init();
    
        curl_setopt_array($curl, array(
    
            CURLOPT_URL => "https://api.coinbase.com/v2/prices/".$chart1."-".$chart2."/spot",
            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));
        curl_close($curl);
      
        return $result->data->amount;
   
    }
}
