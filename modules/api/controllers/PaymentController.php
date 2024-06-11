<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\httpclient\Client;
use app\models\Chart;
use app\models\ChartChain;
use app\models\User;
use app\models\Wallet;
use app\models\History;
use app\models\PaymentType;
use app\models\PaymentDesc;
use app\models\PaymentUser;
use app\models\P2pPayment;
use app\models\P2pHistory;
use app\models\B2bHistory;
use app\models\B2bPayment;
use app\models\P2pAds;
use app\models\WalletAddress;
use CoinRemitter\CoinRemitter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Web3\Web3;
use Web3\Contract;
use Web3\Providers\HttpProvider;




/**
 * Default controller for the `api` module
 */
class PaymentController extends BaseController
{
    const VERIFY_STATUS = [0,1,2];
    const COMISSION_IN = 0; //0% КОМИССИЯ
    const COMISSION_OUT = 0.1; //0.1% КОМИССИЯ

    
    public function actionTest() {

    //$opts = array('http'=>array('method'=>"get", 'header'=>"Accept-language: en\r\n"."Cookie: foo=bar\r\n"));
    //$context = stream_context_create($opts);
    //$file = file_get_contents('http://127.0.0.1:8001/list_currencies', false, $context);
    //return $file;
    $client = new Client();
    $response = $client->createRequest()
    ->setMethod("GET")
    ->setUrl("http://127.0.0.1:8001/list_currencies")
    ->send();
    $result = $response->getContent();

    return $result;


    
    }


    /**
     * @SWG\Post(
     *    path = "/payment/create-payment",
     *    tags = {"Payment"},
     *    summary = "create-payment",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="payment_id",
     *      in="body",
     *      description="create-payment",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price_amount",
     *      in="body",
     *      description="price_amount",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="price_currency",
     *      in="body",
     *      description="price_amount",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="order_id",
     *      in="body",
     *      description="order_id",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="pay_currency",
     *      in="body",
     *      description="pay_currency",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно сохранено",
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
    public function actionCreatePayment() {
        
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $data = [
            "amount" => Yii::$app->request->post("amount",100),
            "currency" => Yii::$app->request->post("currency","usdttrc20"),
            "order_id" => Yii::$app->request->post("order_id","1"),
            "pay_currency" => Yii::$app->request->post("pay_currency","btc")
        ];
        $client = new Client([
            'baseUrl' => 'http://127.0.0.1:8001/',
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        
        $response = $client
        ->post('create_payment', $data)
        
        ->send();
        
        return $response->getContent();

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'http://127.0.0.1:8001/create_payment',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS =>'{
        //         "amount": "'.Yii::$app->request->post("amount", 101).'",
        //         "currency": "'.Yii::$app->request->post("currency", "usdttrc20").'",
        //         "order_id": "'.Yii::$app->request->post("order_id", 1).'",
        //         "pay_currency": "'.Yii::$app->request->post("pay_currency", "btc").'"

        //         }
                
        //         '
        //     ,
        //     CURLOPT_HTTPHEADER => array(
        //         'Content-Type: application/json',
        //       ),
        // ));


        //     $response = curl_exec($curl);

        //     curl_close($curl);

        //     $data = json_decode($response, true);

        //     return $data;
        
    }


    
     /**
     * @SWG\Post(
     *    path = "/payment/create-payout",
     *    tags = {"Payment"},
     *    summary = "create-payout",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="payment_id",
     *      in="body",
     *      description="create-payout",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="address",
     *      in="body",
     *      description="address",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="amount",
     *      in="body",
     *      description="amount",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="currency",
     *      in="body",
     *      description="currency (id 56 - trx)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="ipn_callback_url",
     *      in="body",
     *      description="ipn_callback_url",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно сохранено",
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
    public function actionCreatePayout() {
        
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
        $chain_id = (int)Yii::$app->request->post("currency");
        $history->end_chart_id = 0;
        $history->start_price = (float)Yii::$app->request->post("amount");
        
        if (!Yii::$app->request->post("address")) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Некорректный адрес"];
        }
        $chart = Chart::findOne(['id' => $history->start_chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        $chain = ChartChain::findOne(['id' => $chain_id]);
        $history->payment_id = $chain->id;
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

        $history->end_price = $history->start_price;
        
        $data = [
            "address" => Yii::$app->request->post("address"),
            "amount" => Yii::$app->request->post("amount"),
            "currency" => $chain->symbol
        ];
        $client = new Client([
            'baseUrl' => 'http://127.0.0.1:8001/',
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        
        $response = $client
        ->post('create_payout', $data)
        
        ->send();
        
        $result = json_decode($response->getContent(), true);

        if (isset($result["id"])) {
            $history->ipn_id = $result["id"];
            if(!$history->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка создания запроса", $history];
                }
        
            if(!$wallet->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения счета"];
            }

            return ["success" => true, "message" => "Запрос отправлен в обработку", $result];
            
        } else {
            return ["success" => false, "message" => "Ошибка ", $result];
        }

    
       

    }

    
    public function actionGetAdminBalance() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427';
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/balance',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$api_key
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data;
        
    }


    public function actionGetPayout() {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $api_key = '2WMC682-ATF4WCE-NW0HZNC-5E7S427';
        //$payout_id = "5000885884";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payout/'.Yii::$app->request->get("payout_id"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
              'x-api-key: '.$api_key
            ),
          ));
          $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response, true);

            return $data;
    }


/**
     * @SWG\Get(
     *    path = "/payment/get-payment-status",
     *    tags = {"Payment"},
     *    summary = "get_payment_status",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="type",
     *      in="path",
     *      type="integer",
     *      description="payment_id",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "get_payment_status",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Result")
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
    public function actionGetPaymentStatus() {
        
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;


        
        $client = new Client([
        'baseUrl' => 'http://127.0.0.1:8001/',
        'requestConfig' => [
            'format' => Client::FORMAT_JSON
        ],
        'responseConfig' => [
            'format' => Client::FORMAT_JSON
        ],]);
        $payment_id = Yii::$app->request->get("payment_id");
        $response = $client->get('get_payment_status/'.$payment_id)->send();
        $result=$response;
        return $result->getContent();
    }


    /**
     * @SWG\Get(
     *    path = "/payment/get-payout-status",
     *    tags = {"Payment"},
     *    summary = "get_payout_status",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="type",
     *      in="path",
     *      type="integer",
     *      description="payment_id",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "get_payout_status",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Result")
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
    public function actionGetPayoutStatus_new() {
        
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;


        
        $client = new Client([
        'baseUrl' => 'http://127.0.0.1:8001/',
        'requestConfig' => [
            'format' => Client::FORMAT_JSON
        ],
        'responseConfig' => [
            'format' => Client::FORMAT_JSON
        ],]);
        $payout_id = Yii::$app->request->get("payout_id");
        $response = $client->get('get_payout_status/'.$payout_id)->send();
        $result=$response;
        return $result->getContent();
    

    
    }


    /**
     * @SWG\Get(
     *    path = "/payment/list_currencies",
     *    tags = {"Payment"},
     *    summary = "list_currencies",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Result")
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

    public function actionListCurrencies() {

        $client = new Client();
        $response = $client->createRequest()->setMethod('GET')->setUrl('http://127.0.0.1:8001/list_currencies')->send();
        $result=$response;
        return $result->getContent();

          return $file;
       
    }




    public function actionReadContract() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $abi = '[{"inputs":[],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"from","type":"address"},{"internalType":"address","name":"to","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"}]';
        // $web3 = new Web3(new HttpProvider('https://bsc.blockpi.network/v1/rpc/publiс', 0.1));
        // $contract = new Contract('0x4d57Ce7E453D652DEf305e43536491B4d433f9F1', $abi);
        // $contract->at($contractAddress)->call($functionName, $params, $callback);
//потом вот так обращение идет по адресу контракта, то есть по 0x4d57Ce7E453D652DEf305e43536491B4d433f9F1
        //$contract = new Contract('https://bsc.blockpi.network/v1/rpc/publiс', $abi);
        //An other example for the BSC testnet and with a timeout for the provider (default is 1s and this is short sometimes):

        $providerUri='https://bsc.blockpi.network/v1/rpc/publiс';
        $timeout = 30;
        $provider = new Web3(new HttpProvider($providerUri, 0.1));
        $contract = new Contract($provider, $abi);
        //$contractAddress = '0xFA6987c58AdF0832DCB2E476Abb2586ed749e7DF';
        $contractAddress = '0x4d57Ce7E453D652DEf305e43536491B4d433f9F1';
        //а так надо функцию setTokenPrices вызывать, передавать массив объектов Token { tokenAddress: "", price: uint }
        $data =[
            //"tokenAddress" => '0xFA6987c58AdF0832DCB2E476Abb2586ed749e7DF',
            "address" => '0x4d57Ce7E453D652DEf305e43536491B4d433f9F1',
            //"price" => 1.1
        ];
        // $contract->at($contractAddress)->call('totalSupply', $data, function ($err, $datas) {
        //            if ($err !== null) {
        //             throw new Exception($err->getMessage());
        //             }
        //             echo $datas;
        //         }
        //     );
        // $cost = $contract->at($contractAddress)->call('totalSupply', function($err, $result) {
        //     print_r($result);
        // });
        $contract->getProvider();


        return $contract;
    }


    public function actionCheckBalance() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ipn_key = 'xk8OoaVpKYOWI7mPoeXwl9azuBd+dL4A';
        $api_key = 'THBJKRT-Y5EMJSM-H95YDKQ-1RFRWS8';
        $tid = '477bf661-8cfb-428a-9ba9-1aba92dece9a';

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.nowpayments.io/v1/balance',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'x-api-key: '.$api_key
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true); 
        return $data;
    }

    
    public function actionCheckPayment() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ipn_key = 'xk8OoaVpKYOWI7mPoeXwl9azuBd+dL4A';
        $api_key = 'THBJKRT-Y5EMJSM-H95YDKQ-1RFRWS8';
        $tid = '477bf661-8cfb-428a-9ba9-1aba92dece9a';
        $curl = curl_init();
        // https://api.nowpayments.io/v1/auth
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/auth',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "email": "artivus2@gmail.com",
              "password": "Adm142!@" 
          }',
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json'
            ),
          ));
          
          $response = curl_exec($curl);
          $auth = json_decode($response, true);
          $token = $auth["token"];


        curl_close($curl);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.nowpayments.io/v1/payout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
            "ipn_callback_url": "https://greenavi.com/api/payment/notice-ipn",
            "withdrawals": [
                {
                    "address": "TNdmEpN6AU2oSK7uAoPS4FaqW6okgNLTpk",
                    "currency": "usdttrc20",
                    "amount": 1,
                    "ipn_callback_url": "https://greenavi.com/api/payment/notice-ipn"
                }
            ]
        }',
            CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token,
            'x-api-key: '.$api_key,
            'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true); 

        // $id = Yii::$app->request->get("id");
        // $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => 'https://api.nowpayments.io/v1/payment/?invoiceId='.$id,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'GET',
        //         CURLOPT_HTTPHEADER => array(
        //         'Authorization: Bearer '.$token,
        //         'x-api-key: '.$api_key
        //         ),
        //       ));
            
        //     $response = curl_exec($curl);
        //     curl_close($curl);
        //     $data = json_decode($response, true);
        
        return $data;
        

        

    }

    public function actionFailIpn (){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        

        // $params = [
        //     'coin'=>'TCN', //coin for which you want to use this object.
        //     'api_key'=>'$2y$10$UK8VoHoh/kTDP2u0XW6TDOCYWx87cF0eRmZRyuG35FmsrDgSKkqRy', //api key from coinremitter wallet
        //     'password'=>'12345678' //password for selected wallet
        //  ];
        // $obj = new CoinRemitter($params);

        // $param = [
        //     'invoice_id'=>$history->ipn_id
        // ];
        
        // $invoice = $obj->get_invoice($param);
        
        return "fail";
    }

    public function actionNoticeIpn()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // $status = 0;
        
        


        // if ($status >= 100 || $status == 2) {

        //     $chart = Chart::findOne(["symbol" => $currency]);
        //     if (!$chart) return 'error chart';

        //     $address = WalletAddress::findOne(["value" => $address]);
        //     if (!$address) return 'error address';

        //     $history = new History(["date" => time(), "user_id" => $address->user_id, "status" => 1, "type" => 0]);
        //     $history->start_chart_id = 0;
        //     $history->start_price = 0;
        //     $history->end_chart_id = $chart->id;
        //     $history->end_price = $amount;
        //     if(!$history->save()) return 'error save order';

        //     $wallet = Wallet::findOne(["user_id" => $history->user_id, "chart_id" => $chart->id, "type" => 0]);
        //     if(!$wallet) {
        //         $wallet = new Wallet(["user_id" => $history->user_id, "chart_id" => $chart->id, "balance" => 0, "type" => 0]);
        //     }
        //     $wallet->balance += $history->end_price;

        //     if(!$wallet->save()) return 'error save wallet';
        // } else if ($status < 0) {
        //     // ошибка
        // } else {
        //     // на рассмотрении
        // }
        return "IPN OK";
    }

    public function actionNotice()
    {
        Yii::warning('Оповещение freekassa', 'freekass-notice');

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $MERCHANT_ID = Yii::$app->request->post("MERCHANT_ID");
        $AMOUNT = Yii::$app->request->post("AMOUNT");
        $MERCHANT_ORDER_ID = Yii::$app->request->post("MERCHANT_ORDER_ID");
        $SIGN = Yii::$app->request->post("SIGN");

        $secret_word = '@2}eNgtkMzL}O}I';

        $signHash = md5($MERCHANT_ID . ':' . $AMOUNT . ':' . $secret_word . ':' . $MERCHANT_ORDER_ID);

        if ($signHash != $SIGN) {
            Yii::$app->response->statusCode = 400;
            Yii::warning('wrong sign', 'freekass-notice');
            return 'wrong sign';
        };

        $history =  History::findOne(["id" => $MERCHANT_ORDER_ID, "status" => 0, "type" => 0]);
        if(!$history) {
            Yii::$app->response->statusCode = 400;
            Yii::warning('wrong order', 'freekass-notice');
            return 'wrong order';
        }

        $chart = Chart::findOne($history->end_chart_id);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            Yii::warning('error chart', 'freekass-notice');
            return 'error chart';
        }

        $history->status = 1;
        if(!$history->save()) {
            Yii::$app->response->statusCode = 400;
            Yii::warning('error save order', 'freekass-notice');
            return 'error save order';
        }

        $wallet = Wallet::findOne(["user_id" => $history->user_id, "chart_id" => $chart->id, "type" => 0]);
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $history->user_id, "chart_id" => $chart->id, "balance" => 0, "type" => 0]);
        }
        $wallet->balance += $history->end_price;

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            Yii::warning('error save wallet', 'freekass-notice');
            return 'error save wallet';
        }

        return 'YES';
    }

    /**
     * @SWG\Get(
     *    path = "/payment/list",
     *    tags = {"Payment"},
     *    summary = "Получение списка эмитентов для оплаты Р2Р",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="body",
     *      description="ID валюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список доступных видов платежей",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/PaymentType")
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

        $currency_id = Yii::$app->request->get("currency_id", 1);

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];
        $payment_query = PaymentType::find()->where(["active" => 1, "currency_id" => $currency_id])->all();

        foreach ($payment_query as $payment) {
            $data[] = [
                "id" => $payment->id,
                "name" => $payment->name,
            ];
        }

        return $data;
    }


    /**
     * @SWG\Get(
     *    path = "/payment/cancel-list",
     *    tags = {"Payment"},
     *    summary = "Получение списка отмен оплаты",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="description_id",
     *      in="body",
     *      description="ID отмены",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список причин не оплаты",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/PaymentDesc")
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
    public function actionCancelList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $desc_id = Yii::$app->request->post("description_id");

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];
        $cancel = PaymentDesc::find()->all();

        foreach ($cancel as $payment) {
            $data[] = [
                "id" => $payment->id,
                "text" => $payment->text,
            ];
        }

        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/payment/create",
     *    tags = {"Payment"},
     *    summary = "Создать новый реквизит",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="payment_id",
     *      in="body",
     *      description="Тип платежа",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="body",
     *      description="для b2b 0 - карты, 1 - курьер",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="value",
     *      in="body",
     *      description="Реквизиты платежа",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="payment_receiver",
     *      in="body",
     *      description="ФИО получателя платежа",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="b2b",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="fio_courier",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="phone_courier",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="street_for_courier",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="build_for_courier",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="pod_for_courier",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="summa",
     *      in="body",
     *      description="для b2b",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="history_id",
     *      in="body",
     *      description="id history",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно сохранено",
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
    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $b2b = Yii::$app->request->post("b2b");
        if ((int)$b2b !== 1) {
            
            $payment_id = Yii::$app->request->post("payment_id");
            //$payment = PaymentUser::find()->where(["user_id" => $this->user->id, "payment_id" => $payment_id])->one();
            //if (!$payment) {

            $payment = new PaymentUser(["user_id" => $this->user->id,'payment_id' => $payment_id,'active' => 1]);
            $payment->value = Yii::$app->request->post("value");
            $payment->payment_receiver = Yii::$app->request->post("payment_receiver");
            //$payment->active = 1;

            //} else {
                //$payment->value = Yii::$app->request->post("value") ?? $payment->value;
                //$payment->payment_receiver = Yii::$app->request->post("payment_receiver") ?? $payment->payment_receiver;
                //$payment->active = 1;
            //}


            $payments_count = PaymentUser::find()->where(["user_id" => $this->user->id, "active" => 1])->count();
            


            if ($payments_count > 10) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Превышено максимальное количество способов оплаты", $payments_count];
            }

            if(!$payment->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения способа оплаты"];
            }

        } else {

            
            $payment_id = Yii::$app->request->post("payment_id");
            $fio = Yii::$app->request->post("fio_courier");
            $phone = Yii::$app->request->post("phone_courier");
            $street = Yii::$app->request->post("street_for_courier");
            $build = Yii::$app->request->post("build_for_courier");
            $pod = Yii::$app->request->post("pod_for_courier");
            $description = Yii::$app->request->post("description");
            $summa = Yii::$app->request->post("summa",0);
            $value = Yii::$app->request->post("value");
            $payment_receiver = Yii::$app->request->post("payment_receiver");
            $type = Yii::$app->request->post("type", 1);
            $bank = Yii::$app->request->post("bank");
            

            $b2bpayment = new B2bPayment(["company_id" => $this->user->id, 'payment_id' => $type == 0 ? $payment_id : 2000]);
            $b2bpayment->fio_courier = $fio;
            $b2bpayment->phone_courier = $phone;
            $b2bpayment->street_for_courier = $street;
            $b2bpayment->build_for_courier = $build;
            $b2bpayment->pod_for_courier = $pod;
            $b2bpayment->description = $description;
            $b2bpayment->summa = $summa;
            $b2bpayment->value = $value;
            $b2bpayment->payment_receiver = $payment_receiver;
            $b2bpayment->type = $type;
            $b2bpayment->bank = $bank;
            
            if(!$b2bpayment->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения способа оплаты b2b"];
            }



        }
        


        

        return ["success" => true, "message" => "Способ оплаты / доставки / вывода успешно добавлен"];
    }

    /**
     * @SWG\Post(
     *    path = "/payment/update",
     *    tags = {"Payment"},
     *    summary = "Обновить реквизит",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID реквизита",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="payment_id",
     *      in="body",
     *      description="Тип платежа",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="value",
     *      in="body",
     *      description="Реквизиты платежа",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="payment_receiver",
     *      in="body",
     *      description="ФИО получателя платежа",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="active",
     *      in="body",
     *      description="Активный",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно сохранено",
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
    public function actionUpdate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $payment = PaymentUser::findOne(['id' => Yii::$app->request->post("id"), 'user_id' => $this->user->id]);

        if(!$payment) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Платеж не найден"];
        }

        $payment->payment_id = Yii::$app->request->post("payment_id") ?? $payment->payment_id;
        $payment->value = Yii::$app->request->post("value") ?? $payment->value;
        $payment->payment_receiver = Yii::$app->request->post("payment_receiver") ?? $payment->payment_receiver;
        $payment->active = Yii::$app->request->post("active") ?? $payment->active;

        if(!$payment->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка обновления способа оплаты"];
        }

        return ["success" => true, "message" => "Способ оплаты успешно обновлен"];
    }

    /**
     * @SWG\Get(
     *    path = "/payment/my-list",
     *    tags = {"Payment"},
     *    summary = "Список реквизитов пользователя",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="body",
     *      description="ID валюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Спиоск доступных реквизитов пользователя",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/PaymentUser")
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
    public function actionMyList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];
        $payment_query = PaymentUser::find()->where(["user_id" => $this->user->id, "active" => 1])->all();

        foreach ($payment_query as $payment) {
            $data[] = [
                "id" => $payment->id,
                "name" => $payment->type->name,
                "value" => $payment->value,
                "payment_receiver" => $payment->payment_receiver,
                "payment_id" => $payment->payment_id,
            ];
        }

        return $data;
    }

    /**
     * @SWG\Delete(
     *    path = "/payment/delete",
     *    tags = {"Payment"},
     *    summary = "Удалить реквизит",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID реквизита",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно удалено",
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
    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $id = Yii::$app->request->post("id");
        $payment = PaymentUser::findOne(['id' => $id, 'user_id' => $this->user->id, "active" => 1]);
        if(!$payment) {
            return ["success" => false, "message" => "Платеж не найден"];
        }

        $statuses = [1,2,5];
        $p2p_history = P2pHistory::find()->where(['author_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id])->all();
        if ($p2p_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $p2p_history = P2pHistory::find()->where(['creator_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id])->all();
        if ($p2p_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $b2b_history = B2bHistory::find()->where(['author_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id])->all();
        if ($b2b_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $b2b_history = B2bHistory::find()->where(['creator_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id])->all();
        if ($b2b_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        
        $payment->active = 0;
       
        if(!$payment->save()) {
            return ["success" => false, "message" => "Не удалось удалить платежные реквизиты"];
        }

        return ["success" => true, "message" => "Платежный реквизит успешно удален"];
    }


     /**
     * @SWG\Get(
     *    path = "/payment/courier-list",
     *    tags = {"Payment"},
     *    summary = "Список курьеров или карт",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID курьера",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="company_id",
     *      in="body",
     *      description="ID компании",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="body",
     *      description="Тип type 1 список карт type 0",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список курьеров",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/B2bPayment")
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
    public function actionCourierList()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [];

        
        $id = (int)Yii::$app->request->get("id");
        
        if(!$id) {
            $whereid = ["IS NOT","id", null];
        } else {
            $whereid = ["id" => $id]; 
        }
        $company_id = (int)Yii::$app->request->get("company_id");
        if(!$company_id) {
            $wherecompany = ["IS NOT","company_id", null];
        } else {
            $wherecompany = ["company_id" => $company_id]; 
        }

        $type = (int)Yii::$app->request->get("type");


        $payment_query = B2bPayment::find()
        ->where($wherecompany)
        ->andWhere($whereid)
        ->andWhere(['type' => $type])
        ->all();

        

        return $payment_query;
    }


    /**
     * @SWG\Delete(
     *    path = "/payment/delete-b2bpayment",
     *    tags = {"Payment"},
     *    summary = "Удалить реквизит",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID реквизита",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно удалено",
     *      @SWG\Schema(ref = "#/definitions/B2bPayment")
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
     
    public function actionDeleteB2bpayment()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        
        $id = (int)Yii::$app->request->post("id");
        
        $b2bpayment = B2bPayment::findOne(['id' => $id, 'company_id' => $this->user->id]);
        if(!$b2bpayment->delete()) {
            return ["success" => false, "message" => "Реквизит не найден"];
        } else {
            return ["success" => true, "message" => "Реквизит удален"];
        }
        
    }
}
