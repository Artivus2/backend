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
use app\models\WalletAddress;
use app\models\PaymentUser;
use app\models\PaymentStatus;
use app\models\B2bPayment;
use app\models\User;
use app\models\History;
use app\models\ChartChain;
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
     *      name="currency_id",
     *      in="body",
     *      description="ID валюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="chain_id",
     *      in="body",
     *      description="ID сети",
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

    public function actionGetApiStatus(){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

     public function actionInput()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        // $history = History::find()->where(["user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0])->all();
        // if ($history) {
        //     // Yii::$app->response->statusCode = 400;
        //     // return ["success" => false, "message" => "Завершите предыдущие заявки на пополнение или обратитесь к технической поддержке"];
        //     $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0]);
        // } else {
        //     $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0]);
        // }

        
        // $chart_id = Yii::$app->request->post("chart_id");
        // $history->end_chart_id = $chart_id;
        // $currency_id = Yii::$app->request->post("currency_id", 1);
        // $chain_id = Yii::$app->request->post("chain_id");
        // $history->start_chart_id = $history->end_chart_id;
        
        // $history->start_price = (float)Yii::$app->request->post("price");

        // $chart = Chart::findOne($chart_id);
        // $currency = Currency::findOne($currency_id);
        // $chain = ChartChain::findOne($chain_id);
        // $history->end_price = 0;
        // if (!$chart) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Валюта не найдена"];
        // }
        $ipn_key = 'xk8OoaVpKYOWI7mPoeXwl9azuBd+dL4A';
        $api_key = 'THBJKRT-Y5EMJSM-H95YDKQ-1RFRWS8';
        $tid = '477bf661-8cfb-428a-9ba9-1aba92dece9a';
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/merchant/coins',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key '. $api_key
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response, true);

            foreach ($data as $item) {
                $result[]=$item;
            }
        //echo $response;

        
        
        // if(!$history->save()) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Ошибка создания ссылки"];
        // }
        return $result;
     }


     public function actionInput_old4()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $history = History::find()->where(["user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0])->all();
        if ($history) {
            // Yii::$app->response->statusCode = 400;
            // return ["success" => false, "message" => "Завершите предыдущие заявки на пополнение или обратитесь к технической поддержке"];
            $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0]);
        } else {
            $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 0]);
        }

        
        $chart_id = Yii::$app->request->post("chart_id");
        $history->end_chart_id = $chart_id;
        $currency_id = Yii::$app->request->post("currency_id", 1);
        $chain_id = Yii::$app->request->post("chain_id");
        $history->start_chart_id = $history->end_chart_id;
        
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne($chart_id);
        $currency = Currency::findOne($currency_id);
        $chain = ChartChain::findOne($chain_id);
        $history->end_price = 0;
        // if (!$chart) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Валюта не найдена"];
        // }

        $api_key='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTVRrNE5UWT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiI2M2QzNDYyZjRhY2I0NjUzZGEyYTIwNGQ2YTlmZGJjYmZiZjIyY2NiZjIwYWVlOWI0MWIxODc2Njc4ZTA1Mjk5IiwiZXhwIjo4ODExMDU4MTQ0OH0.X0R_PfjNs2QeecNutTS2EKGwtf0r_LWnf8CKqQA7IUc';
        $shop_id='CghDrxpwxUVFXbq3';
        $url = "https://api.cryptocloud.plus/v2/invoice/create";
        $headers = array(
            "Authorization: Token ".$api_key,
            "Content-Type: application/json"
        );

        $data = array(
            "amount" => $history->start_price,
            "shop_id" => $shop_id,
            "currency" => $currency->symbol,
            "add_fields" =>array(
                       "cryptocurrency" => $chain->symbol
             )
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = [];
        if($http_code == 200){
            $data = json_decode($response, true);

            foreach ($data as $item) {
                $result[]=$item;
            }
            $history->ipn_id = $result[1]["uuid"];
            $history->end_price = $result[1]["amount"];

            //return $result;
        } else {
            echo "Fail: " . $http_code . " " . $response;
        }

        curl_close($ch);

        
        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания ссылки"];
        }
        return $result;
     }

     public function actionInput_old3()
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
        $chain_id = Yii::$app->request->post("chain_id");
        $history->start_chart_id = $history->end_chart_id;
        
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne($history->end_chart_id);
        $chain = ChartChain::findOne($chain_id);
        $history->end_price = 0;
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        //$PAYOUT_KEY='xnPgjY7q9m0WUUMStssqhTBuyVabgKBH0O2uqPsx1FDE15Q00DwhjUylm3IKUzupjG4ivsZJiR2dUEktionhTF0ZPLfZJ7htsHhtHN7NrmVSTY0YVMkm0t4xiIegt8Tb';
        $PAYMENT_KEY = 'oXSoIA8NCt16dsj3qgWzQHtkaf7lqnmHH7ugsGf6o2ABIxLeAA9uopTYrKJKSoWkYXWT3U2ZK34PlhLnP4zQTn6QwNIr2YPSVr9f6m9Ds7SLNciqCm90Sxlf5EBQmYbO';
        $MERCHANT_UUID = '241a6b2f-9705-4014-a378-8638fd37a5ad';
        $payment = \Cryptomus\Api\Client::payment($PAYMENT_KEY, $MERCHANT_UUID);
        //$payout_cryptomus = \Cryptomus\Api\Client::payout($PAYOUT_KEY, $MERCHANT_UUID);
        
        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [
            'amount' => (float)Yii::$app->request->post("price"),
            'currency' => $chart->symbol,
            'network' => $chain->symbol,
            'order_id' => rand(100000000,999999999),
            'url_return' => 'https://greenavi.com/api/payment/notice-ipn',
            'url_callback' => 'https://greenavi.com/api/payment/fail-ipn',
            'is_payment_multiple' => false,
            'lifetime' => '7200',
            'to_currency' => $chart->symbol
        ];
        
        $result = $payment->create($data);

        return $result;


    //     $chart_chain_id = Yii::$app->request->post("chain_id");

    //     $chart_chain = ChartChain::find()->Where(['id'=>$chart_chain_id, "cryptomus" => 1])->one();
    //     $chain_name = Chain::find()->Where(['id'=>$chart_chain->chain_id])->one();
    //     $chart_name = Chart::find()->Where(['id'=>$chart_chain->chart_id])->one();
	//     $qr = '';
    //     if (!$chart_chain) {
    //         Yii::$app->response->statusCode = 400;
    //         return ["success" => false, "message" => "Валюта не найдена"];
    //     }
	//     $result=[];
    //     $result["url"] = "Кошелек существует";
    //     $datauri="Ссылка не будет получена";

       
    //     $wallet = WalletAddress::findOne(["chain_id" => $chart_chain->id, "user_id" => $this->user->id]);
        
    //     $data = [
    //         'network' => $chain_name->name,
    //         'currency' => $chart_name->symbol,
    //         'order_id' => (string)rand(100000000,999999999),
    //         'url_callback' => 'https://greenavi.com/api/wallet/notice-ipn'
    //         ];
    //     $result = $payment_cryptomus->createWallet($data);
    //     if (!$result) {
    //         Yii::$app->response->statusCode = 400;
    //         return ["success" => false, "message" => "Ошибка API Cryptomus"];
    //     }
        
    //     if (!$wallet) {
    //     $wallet = new WalletAddress(["chain_id" => $chart_chain->id, "user_id" => $this->user->id]);
    //     $wallet->value = $result["address"];
    //     $wallet->save();
    //     }

    //     $writer = new PngWriter();

    //     // Create QR code
    //     $qrCode = QrCode::create($result["url"])
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

    //     $resultqr = $writer->write($qrCode, $logo,  $label);
    //     //$result->saveToFile(__DIR__.'/qrcode.png'); 
    //     $datauri = $resultqr->getDataUri();

    //     return ["address" => $wallet->value, "symbol" => $chart_name->symbol, "Network" => $chain_name->name, "qrcode"=>$datauri];
    }


     public function actionInput_old()
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
            'api_key'=> Yii::$app->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
            'password'=> Yii::$app->params['API_KEY_PASSWORD'] //password for selected wallet
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
            'expire_time'=>30,//in minutes,optional,
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
    // 'api_key'=> Yii::app()->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
    // 'password'=>Yii::app()->params['API_KEY_PASSWORD'] //password for selected wallet
    //      ];
    //     $obj = new CoinRemitter($params);
    //     $balance = $obj->get_balance();
        
    //     return $balance;
    // }


    

    /**
     * @SWG\Post(
     *    path = "/wallet/sell",
     *    tags = {"Wallet"},
     *    summary = "Вывести криптовалюту",
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
     *      description="Сумма вывода",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="address",
     *      in="body",
     *      description="адрес кошелька",
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

        $history = History::find()->where(['user_id' => $this->user->id, 'status' => 0, 'type' => 0, 'wallet_direct_id' => 10])->all();
        if ($history) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "У вас уже есть не обработанные заявки на вывод"];
        }
        
        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 10]);

        $history->start_chart_id = (int)Yii::$app->request->post("chart_id");
        $address = trim((int)Yii::$app->request->post("address"));
        $history->ipn_id = $address;
        if (!$address) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Укажите валидный адрес USDT"];
        }


        $history->end_chart_id = 0;
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne(['id' => $history->start_chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        // $id = (int)Yii::$app->request->post("id");
        // $payments = PaymentUser::find()->where(['user_id'=>$this->user->id, 'id' => $id])->all();
        // if (!$payments) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Указан не существующий метод вывода/продажи"];
        // }
        // $history->payment_id = $id;
        
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

        // if (!(float)$this->price($chart->symbol, "RUB")) {
        //     $history->end_price = 0;
        // } else {
        // $history->end_price = (float)$this->price($chart->symbol, "RUB") * $history->start_price / 1;
        // }
        $history->end_price = $history->start_price;

        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка создания запроса"];
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        return ["success" => true, "message" => "Запрос отправлен в обработку"];
    }

/**
     * @SWG\Post(
     *    path = "/wallet/sellb2b",
     *    tags = {"Wallet"},
     *    summary = "Продать/вывести криптовалюту b2b",
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
     *      name="b2bpayments_ids",
     *      in="body",
     *      description="ИД карты продажи/вывода или курьер",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="type_id",
     *      in="body",
     *      description="курьер / карта (0-1)",
     *      required=true,
     *      @SWG\Schema(type="number")
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
    public function actionSellb2b()
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

        $history = History::find()->where(['user_id' => $this->user->id, 'status' => 0, 'type' => 1, 'wallet_direct_id' => 13])->all();
        if ($history) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "У вас уже есть не обработанные заявки на вывод"];
        }
        
        $history = new History(["date" => time(), "user_id" => $this->user->id, "type" => 1, 'wallet_direct_id' => 13]);

        $history->start_chart_id = (int)Yii::$app->request->post("chart_id");
        $history->end_chart_id = 0;
        $history->start_price = (float)Yii::$app->request->post("price");

        $chart = Chart::findOne(['id' => $history->start_chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        //$payments = PaymentUser::find()->where(['user_id'=>$this->user->id, 'payment_id' => 2000])->all();
        
        $b2bpayments = (array)Yii::$app->request->post("b2b_payments_ids");

        $history->payment_id = (int)Yii::$app->request->post("type_id", 0);
        $history->ipn_id = implode(",", $b2bpayments);
        
        $history->status = 0;




        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" => $chart->id,'type' => 1]); //b2b
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

        if (!(float)$this->price($chart->symbol, "RUB")) {
            $history->end_price = 0;
        } else {
        //$history->end_price = (float)$this->price($chart->symbol, "RUB") * $history->start_price / 1;
        $history->end_price = $history->start_price;
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
     *    path = "/wallet/update-summa",
     *    tags = {"Wallet"},
     *    summary = "b2b обновление суммы карт",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID карты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="summa",
     *      in="body",
     *      description="Сумма на карте",
     *      required=true,
     *      @SWG\Schema(type="number")
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
    public function actionUpdateSumma()
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
        $id = Yii::$app->request->post("id");
        $summa = Yii::$app->request->post("summa");
        $b2bpayments = B2bPayment::find()->where(['company_id' => $this->user->id,'id' => $id])->one();
        $b2bpayments->summa = $summa;
        if(!$b2bpayments->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения суммы"];
        }

        return ["success" => true, "message" => "сумма обновлена"];
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
     * @SWG\Get(
     *    path = "/wallet/history",
     *    tags = {"Wallet"},
     *    summary = "История переводов, ввода и выводов",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="integer",
     *      description="id сделки",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="path",
     *      type="integer",
     *      description="Счет исходящий (0	Финансовый, 1	B2B )",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="wallet_direct_id",
     *      in="path",
     *      type="integer",
     *      description="10 - вывод общий счет, 11 (ввод freekassa, ввода coinremitter), 13 - вывод b2b , (0,1 - переводы)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="status",
     *      in="path",
     *      type="integer",
     *      description="0 - создан / в обработке, 1 - выполнено, 2 - отменен, -1 (завершен с coinremitter)",
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
    public function actionHistory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        //$statusIDs = array();
        //$status = Yii::$app->request->get("status");
        //$statusIDs = explode(",", $status);
        $status = (array)Yii::$app->request->get("status");
        if(!$status) {
            $wherestatus = ["IS NOT", "status", null];
        } else {
            $wherestatus = ["in", "status", $status];
        }

        $type = (array)Yii::$app->request->get("type");
        if(!$type) {
            $wheretype = ["IS NOT", "type", null];
        } else {
            $wheretype = ["in", "type", $type];
        }

        $wallet_direct_id = (int)Yii::$app->request->get("wallet_direct_id");
        if(!$wallet_direct_id) {
            $wherewdi = ["in", "wallet_direct_id", [0,1]];
        } else {
            if ((int)$wallet_direct_id == 11) {
                $wherewdi = ["in", "wallet_direct_id", [11,12]];    
            }
            if ((int)$wallet_direct_id == 10) {
                $wherewdi = ["wallet_direct_id" => 10];    
            }
            if ((int)$wallet_direct_id == 13) {
                $wherewdi = ["wallet_direct_id" => 13];    
            }
        }

        $id = (int)Yii::$app->request->get("id");
        if(!$id) {
            $whereid = ["IS NOT", "id", null];
        } else {
            $whereid = ["in", "id", $id];
        }


        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        
        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }
        
        $data = [];
        //$history_query = History::find()->joinWith(["startChart", "endChart"])->where(["user_id" => $this->user->id, "status" => $status])->all();
        $history_query = History::find()
        ->where(["user_id" => $this->user->id])
        ->andWhere($whereid)
        ->andWhere($wheretype)
        ->andWhere($wherewdi)
        ->andWhere($wherestatus)
        ->orderBy("date DESC")->all();

        foreach ($history_query as $history) {
            $status = PaymentStatus::findOne(['type' => $history->wallet_direct_id,'status_id' => $history->status]);
            $data[] = [
                "id" => $history->id,
                "type" => $history->walletType->title,
                "status" => $status->title,
                "date" => date("Y-m-d H:i:s", $history->date),
                "start_symbol" => isset($history->startChart) ? $history->startChart->symbol : "RUB",
                "start_price" => $history->start_price,
                "end_symbol" => isset($history->endChart) ? $history->endChart->symbol : "RUB",
                "end_price" => $history->end_price,
                "wallet_direct_id" => $history->directType->title,
                "payment_id" => $history->paymentType->name ?? '-'
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
            $balance = $wallet->balance;
            if ((float)$balance > 1) {
                $result = number_format($balance, 2,'.','');
            }
            else {
                $result = number_format($balance, 10,'.','');
            }

            $blocked = $wallet->blocked;
            if ((float)$blocked > 1) {
                $result_2 = number_format($blocked, 2,'.','');
            }
            else {
                $result_2 = number_format($blocked, 10,'.','');
            }

            $data[] = [
                "id" => $wallet->chart_id,
                "name" => $wallet->chart->name,
                "symbol" => $wallet->chart->symbol,
                "price" => (float)$this->price($wallet->chart->symbol, "USD"),
                "balance" => $result,
                "blocked" => $result_2,
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

        $result = WalletType::find()->where(['active' => 1])->all();

        

        return $result;
     }
    
    
     protected function price($chart1, $chart2){
        
        if ($chart1 == "CLV") {
            $chart1 = "USDT";
        }
        // if ($chart2 == "") {
        //     $chart2 = "USDT";
        // }

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
      
        if ((float)$result->data->amount >= 1) {
            $result = number_format($result->data->amount, 2,'.','');
        }
        else {
            $result = number_format($result->data->amount, 10,'.','');
        }
        
        return $result;
   
    }
}
