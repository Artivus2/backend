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
use app\models\P2pAds;
use app\models\WalletAddress;

/**
 * Default controller for the `api` module
 */
class PaymentController extends BaseController
{
    public function actionNoticeIpn()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $cp_merchant_id = '';
        $cp_ipn_secret = '76479a5aF47AAaEf758Cb1297880FB59Cb724f62012c3E1b1f7685cF3Ab4Db91';

        Yii::$app->mailer->compose()
            ->setTo("crypto.zeta2@gmail.com")
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setSubject("Успешное пополнение")
            ->setTextBody(json_encode(Yii::$app->request->post()))
            ->send();

        $merchant = Yii::$app->request->post("merchant");
        $hmac = Yii::$app->request->post("hmac");
        $address = Yii::$app->request->post("address");
        $amount = floatval(Yii::$app->request->post("amount"));
        $currency = Yii::$app->request->post("currency");
        $status = Yii::$app->request->post("status");


        if ($hmac != 'hmac') {
            return 'IPN Mode is not HMAC';
        }

        if (empty($_SERVER['HTTP_HMAC'])) {
            return 'No HMAC signature sent.';
        }

        $request = file_get_contents('php://input');
        if (empty($request)) {
            return 'Error reading POST data';
        }

        if ($merchant != $cp_merchant_id) {
            return 'No or incorrect Merchant ID passed';
        }

        $hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
        if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
            return 'HMAC signature does not match';
        }

        if ($status >= 100 || $status == 2) {

            $chart = Chart::findOne(["symbol" => $currency]);
            if (!$chart) return 'error chart';

            $address = WalletAddress::findOne(["value" => $address]);
            if (!$address) return 'error address';

            $history = new History(["date" => time(), "user_id" => $address->user_id, "status" => 1, "type" => 1]);
            $history->start_chart_id = 0;
            $history->start_price = 0;
            $history->end_chart_id = $chart->id;
            $history->end_price = $amount;
            if(!$history->save()) return 'error save order';

            $wallet = Wallet::findOne(["user_id" => $history->user_id, "chart_id" => $chart->id, "type" => 0]);
            if(!$wallet) {
                $wallet = new Wallet(["user_id" => $history->user_id, "chart_id" => $chart->id, "balance" => 0, "type" => 0]);
            }
            $wallet->balance += $history->end_price;

            if(!$wallet->save()) return 'error save wallet';
        } else if ($status < 0) {
            // ошибка
        } else {
            // на рассмотрении
        }
        return 'IPN OK';
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

        $history =  History::findOne(["id" => $MERCHANT_ORDER_ID, "status" => 0, "type" => 1]);
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
     *      description = "Спиоск доступных видов платяжей",
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

        return ["success" => true, "message" => "Способ оплаты успешно добавлен"];
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
            return ["success" => false, "message" => "Способ оплаты не найден"];
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
     * @SWG\Post(
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
    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $payment = PaymentUser::findOne(['id' => Yii::$app->request->post("id"), 'user_id' => $this->user->id, "active" => 1]);
        if(!$payment) {
            return ["success" => false, "message" => "Платеж не найден"];
        }

        
        // $statuses = [1,2,3,4,5,7,8,9];
        // $p2p_payments = P2pPayment::find()->where(["user_id" => $this->user->id, "payment_id" => $payment->payment_id])->all();
        // foreach ($p2p_payments as $p2p_payment) {
        //     $p2p_ads_s = P2pAds::find()->where(["id" => $p2p_payment->p2p_ads_id])->andWhere(["in","status", $statuses])->one();
        //     if ($p2p_ads_s) {
        //         Yii::$app->response->statusCode = 401;
        //         return ["success" => false, "message" => "Реквизит не может быть удален, есть активные или не завершенные ордера", "Текущий статус" => $p2p_ads_s->status];
        //     }

        // }
        $payment->active = 0;
       
        if(!$payment->save()) {
            return ["success" => false, "message" => "Не удалось удалить платежные реквизиты"];
        }

        return ["success" => true, "message" => "Платежный реквизит успешно удален"];
    }
}
