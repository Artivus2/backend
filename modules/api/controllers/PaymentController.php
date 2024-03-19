<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\User;
use app\models\Wallet;
use app\models\History;
use app\models\PaymentType;
use app\models\PaymentDesc;
use app\models\PaymentUser;
use app\models\P2pPayment;
use app\models\B2bPayment;
use app\models\P2pAds;
use app\models\WalletAddress;
use CoinRemitter\CoinRemitter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Default controller for the `api` module
 */
class PaymentController extends BaseController
{
    
    public function actionCheckPayment() {
        //Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // $history = History::find()->where(["user_id" => $this->user->id, "type" => 0, 'wallet_direct_id' => 12, 'status' => 3])->one();
        // if (!$history) {
        //     Yii::$app->response->statusCode = 401;
        //     return ["success" => false, "message" => "Платеж не найден, обратитесь к администратору"];
        // }

        // $params = [
        //     'coin'=>'TCN', //coin for which you want to use this object.
        //     'api_key'=> Yii::$app->params['API_KEY_COINREMITTER'], //api key from coinremitter wallet
        //     'password'=> Yii::$app->params['API_KEY_PASSWORD'] //password for selected wallet
        //  ];
        // $obj = new CoinRemitter($params);

        // $param = [
        //     'invoice_id'=>$history->ipn_id
        // ];
        
        // $invoice = $obj->get_invoice($param);
        // $coin = $invoice["data"]["coin"];
        // $base_currency = $invoice["data"]["base_currency"];
        // $paid_amount = $invoice["data"]["paid_amount"][$coin] ?? 0;
        // $total_amount = $invoice["data"]["total_amount"][$coin] ?? 0;
        // $data[] = [
        //     "coin" => $coin,
        //     "base_currency" => $base_currency,
        //     "paid_amount" => (float)$paid_amount,
        //     "total_amount" => (float)$total_amount,
        //     "status_code" => $invoice["data"]["status_code"]
        // ];
        $id = Yii::$app->request->get("id");
        $api_key='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiTVRrNE5UWT0iLCJ0eXBlIjoicHJvamVjdCIsInYiOiI2M2QzNDYyZjRhY2I0NjUzZGEyYTIwNGQ2YTlmZGJjYmZiZjIyY2NiZjIwYWVlOWI0MWIxODc2Njc4ZTA1Mjk5IiwiZXhwIjo4ODExMDU4MTQ0OH0.X0R_PfjNs2QeecNutTS2EKGwtf0r_LWnf8CKqQA7IUc';
        $shop_id='CghDrxpwxUVFXbq3';
        //$url = "https://api.cryptocloud.plus/v2/invoice/create";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.cryptocloud.plus/v2/invoice/merchant/info");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            "uuids" => array($id)
        )));

        $headers = array(
            "Authorization: Token ".$api_key,
            "Content-Type: application/json"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                curl_close($ch);


                //return $response;
            } else {
                echo "Fail: " . $statusCode . " " . $response;
            }
        }
        $data = json_decode($response, true);
        $result = [];
        foreach ($data as $item){
            $result[] = $item;
        }

        return $result[1][0];
        // {"status":"success","result":{"uuid":"INV-9VBKMAQR","created":"2024-03-18 12:46:17.729941","address":"","expiry_date":"2024-03-19 12:46:17.718070","side_commission":"client","side_commission_service":"merchant","type_payments":"crypto","amount":0.11,"amount_usd":0.11,"amount_in_fiat":10.0,"fee":1.4,"fee_usd":1.4,"service_fee":0.00209,"service_fee_usd":0.0,"fiat_currency":"RUB","status":"created","is_email_required":false,"link":"https://pay.cryptocloud.plus/9VBKMAQR","invoice_id":null,"currency":{"id":4,"code":"USDT","fullcode":"USDT_TRC20","network":{"code":"TRC20","id":4,"icon":"https://cdn.cryptocloud.plus/currency/crypto/TRX.svg","fullname":"Tron"},"name":"Tether","is_email_required":false,"stablecoin":true,"icon_base":"https://cdn.cryptocloud.plus/currency/icons/main/usdt.svg","icon_network":"https://cdn.cryptocloud.plus/icons-currency/USDT-TRC20.svg","icon_qr":"https://cdn.cryptocloud.plus/currency/icons/stroke/usdt.svg","order":1},"project":{"id":352403,"name":"GREENAVI","fail":"https://greenavi.com/api/payment/fail-ipn","success":"https://greenavi.com/api/payment/success-ipn","logo":""},"test_mode":true}}

        

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
            

            $payment = PaymentUser::find()->where(["user_id" => $this->user->id, "payment_id" => $payment_id])->one();
            if (!$payment) {
                
                $payment = new PaymentUser(["user_id" => $this->user->id]);
                $payment->payment_id = $payment_id;
                $payment->value = Yii::$app->request->post("value");
            
                $payment->payment_receiver = Yii::$app->request->post("payment_receiver");
                $payment->active = 1;

            } else {
                $payment->value = Yii::$app->request->post("value") ?? $payment->value;
                $payment->payment_receiver = Yii::$app->request->post("payment_receiver") ?? $payment->payment_receiver;
                $payment->active = 1;
            }


            $payments_count = PaymentUser::find()->where(["user_id" => $this->user->id, "active" => 1])->count();
            


            if ($payments_count > 14) {
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
            $summa = Yii::$app->request->post("summa");
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
        $p2p_history = P2pHistory::find()->where(['author_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id, 'active' => 1])->all();
        if ($p2p_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $p2p_history = P2pHistory::find()->where(['creator_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id, 'active' => 1])->all();
        if ($p2p_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $b2b_history = B2bHistory::find()->where(['author_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id, 'active' => 1])->all();
        if ($b2b_history) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера"];    
        }
        $b2b_history = B2bHistory::find()->where(['creator_id' => $this->user->id, 'status' => $statuses,'payment_id' => $id, 'active' => 1])->all();
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
