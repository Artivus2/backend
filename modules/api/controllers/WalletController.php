<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\Chain;
use app\models\Wallet;
use app\models\PaymentUser;
use app\models\WalletInv;
use app\models\WalletTrade;
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

    /**
     * @SWG\Post(
     *    path = "/wallet/input",
     *    tags = {"Wallet"},
     *    summary = "Ввод криптовалюты",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID криптовалюты и сети",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Адрес кошелька",
     *      @SWG\Definition(
     *         required={"address"},
     *         @SWG\Property(
     *             property="address",
     *             type="string"
     *         ),
     *         required={"symbol"},
     *         @SWG\Property(
     *             property="symbol",
     *             type="string"
     *         ),
     *         required={"network"},
     *         @SWG\Property(
     *             property="network",
     *             type="string"
     *         ),
     
     * 		   required={"qrcode"},
     *         @SWG\Property(
     *             property="qrcode",
     *             type="string"
     *         )

     *      )
     *    ),
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

     public function actionCallBack()
     {
        Yii::$app->response->statusCode = 401;
        return ["success" => false, "message" => "Платеж не произведен"];
     }

    public function actionInput()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $PAYOUT_KEY='';
        $PAYMENT_KEY = '';
        $MERCHANT_UUID = 'b2b19ba5-7879-4135-8d48-dfb165d8f904';
        $payment_cryptomus = \Cryptomus\Api\Client::payment($PAYMENT_KEY, $MERCHANT_UUID);
        $payout_cryptomus = \Cryptomus\Api\Client::payout($PAYOUT_KEY, $MERCHANT_UUID);
        
        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chart_chain_id = Yii::$app->request->post("chain_id");

        $chart_chain = ChartChain::find()->Where(['id'=>$chart_chain_id, "cryptomus" => 1])->one();
        $chain_name = Chain::find()->Where(['id'=>$chart_chain->chain_id])->one();
        $chart_name = Chart::find()->Where(['id'=>$chart_chain->chart_id])->one();
	    $qr = '';
        if (!$chart_chain) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }
	    $result=[];
        $result["url"] = "Кошелек существует";
        $datauri="Ссылка не будет получена";

       
        $wallet = WalletAddress::findOne(["chain_id" => $chart_chain->id, "user_id" => $this->user->id]);
        
        $data = [
            'network' => $chain_name->name,
            'currency' => $chart_name->symbol,
            'order_id' => (string)rand(100000000,999999999),
            'url_callback' => 'https://greenavi.com/api/wallet/call-back'
            ];
        $result = $payment_cryptomus->createWallet($data);
        if (!$result) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка API Cryptomus"];
        }
        
        if (!$wallet) {
        $wallet = new WalletAddress(["chain_id" => $chart_chain->id, "user_id" => $this->user->id]);
        $wallet->value = $result["address"];
        $wallet->save();
        }

        $writer = new PngWriter();

        // Create QR code
        $qrCode = QrCode::create($result["url"])
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
        ->setSize(300)
        ->setMargin(10)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));

        // Create generic logo
        $logo = Logo::create(__DIR__.'/home-logo.png')
        ->setResizeToWidth(10)
        ->setPunchoutBackground(true);
        
        // Create generic label
        $label = Label::create('GREENAVI')
        ->setTextColor(new Color(255, 255, 255));

        $resultqr = $writer->write($qrCode, $logo,  $label);
        //$result->saveToFile(__DIR__.'/qrcode.png'); 
        $datauri = $resultqr->getDataUri();

        return ["address" => $wallet->value, "symbol" => $chart_name->symbol, "Network" => $chain_name->name, "qrcode"=>$datauri];
    }

    public function actionInput_old()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $chain_id = Yii::$app->request->post("chain_id");

        $chain = ChartChain::find()->Where(['id'=>$chain_id])->one();
	    $qr = '';
        if (!$chain) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }



        $wallet = WalletAddress::findOne(["chain_id" => $chain->id, "user_id" => $this->user->id]);

    //     if (!$wallet) {
    //         $cps_api = new CoinpaymentsAPI('76479a5aF47AAaEf758Cb1297880FB59Cb724f62012c3E1b1f7685cF3Ab4Db91', 'fdc2cb0894961d95b7bace09cdd7aeab28171ba4d961a3b54c4ff23fa6ecdf9e', 'json');
    //         //$cps_api->Setup('76479a5aF47AAaEf758Cb1297880FB59Cb724f62012c3E1b1f7685cF3Ab4Db91', 'fdc2cb0894961d95b7bace09cdd7aeab28171ba4d961a3b54c4ff23fa6ecdf9e');
    //         //$data = $cps_api->GetCallbackAddressWithIpn($chain->symbol,  Url::to(["/api/payment/notice-ipn"], "https"));
	//     //if ($chain->symbol=="BTC.LN") {$chain->symbol = "BTC";}
	//     $data = $cps_api->GetDepositAddress($chain->symbol);

    //         $wallet = new WalletAddress(["chain_id" => $chain->id, "user_id" => $this->user->id]);
    //         $wallet->value = $data["result"]["address"];
	    
    //         $wallet->save();
	// }
	
    //     $writer = new PngWriter();

    //     // Create QR code
    //     $qrCode = QrCode::create($wallet->value)
    //     ->setEncoding(new Encoding('UTF-8'))
    //     ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
    //     ->setSize(300)
    //     ->setMargin(10)
    //     ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
    //     ->setForegroundColor(new Color(0, 0, 0))
    //     ->setBackgroundColor(new Color(255, 255, 255));

    //     // Create generic logo
    //     $logo = Logo::create(__DIR__.'/home-logo.png')
    //     ->setResizeToWidth(10)
    //     ->setPunchoutBackground(true);
        
    //     // Create generic label
    //     $label = Label::create('GREENAVI')
    //     ->setTextColor(new Color(255, 255, 255));

    //     $result = $writer->write($qrCode, $logo,  $label);
    //     //$result->saveToFile(__DIR__.'/qrcode.png'); 
    //     $datauri = $result->getDataUri();


        return ["address" => $wallet->value, "chain" => $chain->symbol, "qrcode"=>$datauri];
    }



    

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
            CURLOPT_URL => "https://api.binance.com/api/v3/ticker/price?symbol=" . $chart->symbol . "RUB",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
        ));

        $result = json_decode(curl_exec($curl));

        curl_close($curl);

        $history->end_price = 1 * $history->start_price / (float)$result->price;

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
     
     *      description="Тип кошелька (финансовый(1), инвестиционный(2), торговый(3), 0 - общий список), 10 - на вывод (заморожено)",
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


        $wallettype = Yii::$app->request->get("wallettype");
        $all = array(0,1,2,3,10);
        if(!in_array($wallettype, $all)) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Неверно выбран тип кошелька"];
        }



        $type = 5; //b2b рубль

        if ($wallettype == 10){
            
            $wallet_query_fin = Wallet::find()->joinWith("chart")->where(["active" => 1, 'wallet.user_id' => $this->user->id])->andWhere([">", "wallet.balance", 0])
            ->andWhere(["=", "wallet.type", 10])
            ->all();
            return $wallet_query_fin;
        }

        if ($wallettype == 1){
            $data = [];
            $nodata = false;
            $wallet_query_fin = Wallet::find()->joinWith("chart")->where(["active" => 1, 'wallet.user_id' => $this->user->id])->andWhere([">", "wallet.balance", 0])->all();
            $wallers = [];
            $symbols = [];
            foreach ($wallet_query_fin as $wallet) {
                if((int)$wallet->chart_id == 2024) {
                    $data[] = [
                        "id" => $wallet->chart_id,
                        "name" => $wallet->chart->symbol,
                        "symbol" => $wallet->chart->name,
                        "price" => 1,
                        "balance" => $wallet->balance,
                        "blocked" => $wallet->blocked ?? null,
                        "percent" => "0",
                        "wallet" => "fin",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }

            }
            
            foreach ($wallet_query_fin as $wallet) {
                if($wallet->chart->symbol == "RUB") {
                    continue;
                }

                if($wallet->chart->symbol != "USDT") {
                    
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }

            }
            if(count($wallet_query_fin) == 0) return $data;
            if (!empty($symbols)) {
                $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($res);
                } else {
                    $nodata = true;
                }
    
            if(isset($wallers["USDT"])) {
                $wallet = $wallers["USDT"];
                $data[] = [
                    "id" => $wallet->chart->id,
                    "name" => $wallet->chart->name,
                    "symbol" => $wallet->chart->symbol,
                    "price" => "1",
                    "balance" => $wallet->balance,
                    "blocked" => $wallet->blocked ?? null,
                    "percent" => "0",
                    "wallet" => "fin",
                    "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                ];
            }
            if (!$nodata) {
                foreach ($result as $item) {
                    $wallet = $wallers[$item->symbol];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => $item->lastPrice,
                        "balance" => $wallet->balance,
                        "blocked" => $wallet->blocked ?? null,
                        "percent" => $item->priceChangePercent,
                        "wallet" => "fin",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];

                }
            }

        }

        if ($wallettype == 2){
            $data = [];
            $nodata = false;
            $wallet_query_trade = WalletTrade::find()->joinWith("chart")->where(["active" => 1, 'wallettrade.user_id' => $this->user->id])->andWhere([">", "wallettrade.balance", 0])->all();
            $wallers = [];
            $symbols = [];
         
            foreach ($wallet_query_trade as $wallet) {
                
                if($wallet->chart->symbol != "USDT") {
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }
            }


            if(count($wallet_query_trade) == 0) return $data;
            if (!empty($symbols)) {
                $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($res);
                } else {
                    $nodata = true;
                }
    
                if(isset($wallers["USDT"])) {
                    $wallet = $wallers["USDT"];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => "1",
                        "balance" => $wallet->balance,
                        "percent" => "0",
                        "wallet" => "trade",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }
                if (!$nodata) {
                    foreach ($result as $item) {
                        $wallet = $wallers[$item->symbol];
                        $data[] = [
                            "id" => $wallet->chart->id,
                            "name" => $wallet->chart->name,
                            "symbol" => $wallet->chart->symbol,
                            "price" => $item->lastPrice,
                            "balance" => $wallet->balance,
                            "percent" => $item->priceChangePercent,
                            "wallet" => "trade",
                            "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                        ];
    
                    }
                }
            

            
        }

        if ($wallettype == 3){
            $data = [];
            $nodata = false;
            $wallet_query_inv = WalletInv::find()->joinWith("chart")->where(["active" => 1, 'walletInv.user_id' => $this->user->id])->andWhere([">", "walletInv.balance", 0])->all();
            $wallers = [];
            $symbols = [];
            foreach ($wallet_query_inv as $wallet) {
                if($wallet->chart->symbol != "USDT") {
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }
            }
            if(count($wallet_query_inv) == 0) return $data;
            if (!empty($symbols)) {
            $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $res = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($res);
            } else {
                $nodata = true;
            }

            if(isset($wallers["USDT"])) {
                $wallet = $wallers["USDT"];
                $data[] = [
                    "id" => $wallet->chart->id,
                    "name" => $wallet->chart->name,
                    "symbol" => $wallet->chart->symbol,
                    "price" => "1",
                    "balance" => $wallet->balance,
                    "percent" => "0",
                    "wallet" => "inv",
                    "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                ];
            }
            if (!$nodata) {
                foreach ($result as $item) {
                    $wallet = $wallers[$item->symbol];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => $item->lastPrice,
                        "balance" => $wallet->balance,
                        "percent" => $item->priceChangePercent,
                        "wallet" => "inv",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];

                }
            }

        }

        if ($wallettype == 0){

        
            $data= [];
            $wallet_query_fin = Wallet::find()->joinWith("chart")->where(["active" => 1, 'wallet.user_id' => $this->user->id])->andWhere([">", "wallet.balance", 0])->all();
            $wallers = [];
            $symbols = [];
            $nodata = false;
            foreach ($wallet_query_fin as $wallet) {
                if((int)$wallet->chart_id == 2024) {
                    $data[] = [
                        "id" => $wallet->chart_id,
                        "name" => $wallet->chart->symbol,
                        "symbol" => $wallet->chart->name,
                        "price" => 1,
                        "balance" => $wallet->balance,
                        "blocked" => $wallet->blocked ?? null,
                        "percent" => "0",
                        "wallet" => "fin",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }

            }
            foreach ($wallet_query_fin as $wallet) {
                if($wallet->chart->symbol == "RUB") {
                    continue;
                }
                if($wallet->chart->symbol != "USDT") {
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }
            }
            if(count($wallet_query_fin) == 0) return $data;
            if (!empty($symbols)) {
                $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($res);
                } else {
                    $nodata = true;
                }
    
                if(isset($wallers["USDT"])) {
                    $wallet = $wallers["USDT"];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => "1",
                        "balance" => $wallet->balance,
                        "blocked" => $wallet->blocked ?? null,
                        "percent" => "0",
                        "wallet" => "fin",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }
                if (!$nodata) {
                    foreach ($result as $item) {
                        $wallet = $wallers[$item->symbol];
                        $data[] = [
                            "id" => $wallet->chart->id,
                            "name" => $wallet->chart->name,
                            "symbol" => $wallet->chart->symbol,
                            "price" => $item->lastPrice,
                            "balance" => $wallet->balance,
                            "blocked" => $wallet->blocked ?? null,
                            "percent" => $item->priceChangePercent,
                            "wallet" => "fin",
                            "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                        ];
    
                    }
                }

        
            $wallet_query_trade = WalletTrade::find()->joinWith("chart")->where(["active" => 1, 'wallettrade.user_id' => $this->user->id])->andWhere([">", "wallettrade.balance", 0])->all();
            $wallers = [];
            $symbols = [];
            $nodata = false;
            foreach ($wallet_query_trade as $wallet) {
                if($wallet->chart->symbol != "USDT") {
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }
            }


            if(count($wallet_query_trade) == 0) return $data;
            if (!empty($symbols)) {
                $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($res);
                } else {
                    $nodata = true;
                }
    
                if(isset($wallers["USDT"])) {
                    $wallet = $wallers["USDT"];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => "1",
                        "balance" => $wallet->balance,
                        "percent" => "0",
                        "wallet" => "trade",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }
                if (!$nodata) {
                    foreach ($result as $item) {
                        $wallet = $wallers[$item->symbol];
                        $data[] = [
                            "id" => $wallet->chart->id,
                            "name" => $wallet->chart->name,
                            "symbol" => $wallet->chart->symbol,
                            "price" => $item->lastPrice,
                            "balance" => $wallet->balance,
                            "percent" => $item->priceChangePercent,
                            "wallet" => "trade",
                            "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                        ];
    
                    }
                }

            $wallet_query_inv = WalletInv::find()->joinWith("chart")->where(["active" => 1, 'walletInv.user_id' => $this->user->id])->andWhere([">", "walletInv.balance", 0])->all();
            $wallers = [];
            $symbols = [];
            foreach ($wallet_query_inv as $wallet) {
                if($wallet->chart->symbol != "USDT") {
                    $symbol = $wallet->chart->symbol . "USDT";
                    $symbols[] = $symbol;
                    $wallers[$symbol] = $wallet;
                } else {
                    $wallers["USDT"] = $wallet;
                }
            }
            if(count($wallet_query_inv) == 0) return $data;
            if (!empty($symbols)) {
                $ch = curl_init("https://api.binance.com/api/v3/ticker/24hr?symbols=" . json_encode($symbols));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($res);
                } else {
                    $nodata = true;
                }
    
                if(isset($wallers["USDT"])) {
                    $wallet = $wallers["USDT"];
                    $data[] = [
                        "id" => $wallet->chart->id,
                        "name" => $wallet->chart->name,
                        "symbol" => $wallet->chart->symbol,
                        "price" => "1",
                        "balance" => $wallet->balance,
                        "percent" => "0",
                        "wallet" => "inv",
                        "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                    ];
                }
                if (!$nodata) {
                    foreach ($result as $item) {
                        $wallet = $wallers[$item->symbol];
                        $data[] = [
                            "id" => $wallet->chart->id,
                            "name" => $wallet->chart->name,
                            "symbol" => $wallet->chart->symbol,
                            "price" => $item->lastPrice,
                            "balance" => $wallet->balance,
                            "percent" => $item->priceChangePercent,
                            "wallet" => "inv",
                            "icon" => Url::to(["/images/icons/" . $wallet->chart->symbol . ".png"], "https"),
                        ];
    
                    }
                }
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

}
