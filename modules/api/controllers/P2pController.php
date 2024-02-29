<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Chart;
use app\models\Currency;
use app\models\User;
use app\models\Wallet;
use app\models\P2pAds;
use app\models\P2pHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\PaymentUser;
use app\models\StatusType;
use app\models\P2pPayment;
use app\components\Cryptomus\Client;
use app\components\Cryptomus\Payment;


class P2pController extends BaseController
{
    const PENDING = -1;
    const TRADING = 1;
    const BUYER_PAYED = 2;
    const COMPLETED = 4;
    const IN_APPEAL = 5;
    const CANCELLED = 6;
    const CANCELLED_BY_SYSTEM = 7;
    const BLOCKED_IN_ACTION = 8;
    const REMOVED = 9;
    const ALL_COMPLETED = 10;
    const SELL = 2;
    const BUY = 1;
    const VERIFY_STATUS = [0,1,2];

    
    /**
     * @SWG\Post(
     *    path = "/p2p/create-sell",
     *    tags = {"p2p"},
     *    summary = "Создать обьявление о продаже криптовалюты на p2p (SELL)",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="body",
     *      description="Валюта (по умолчанию рубль)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="amount",
     *      in="body",
     *      description="Количество (в криптовалюте)",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="course",
     *      in="body",
     *      description="Курс",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="min_limit",
     *      in="body",
     *      description="Минимальное количество (min_limit) (в валюте)",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="max_limit",
     *      in="body",
     *      description="Максимальное количество (max_limit) (в валюте)",
     *      @SWG\Schema(type="number")
     *     ),
     
     *    @SWG\Parameter(
     *      name="duration",
     *      in="body",
     *      description="Период оплаты",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="payments",
     *      in="body",
     *      description="Способы оплаты (до 10)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="условия сделки",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Ордер на продажу создан",
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

    //  public function actionUserid()
    //  {
    //     return $this->user->id;
    //  }
   

     public function actionCreateSell()
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

        $status = -1;  //создать ордер
        $p2p = new P2pAds(["date" => time(), "user_id" => $this->user->id]);
        $p2p->currency_id = Yii::$app->request->post("currency_id",1);
        $p2p->description = Yii::$app->request->post("description", 'стандартные условия');
        


        
        $p2p->amount = (float)Yii::$app->request->post("amount"); //количество для продажи
        $p2p->start_amount = $p2p->amount;
        if(!$p2p->amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указано количество"];
        }


        $p2p->course = (float)Yii::$app->request->post("course"); //курс
        if(!$p2p->course) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан курс"];
        }

        $p2p->min_limit = (float)Yii::$app->request->post("min_limit");
        if(!$p2p->min_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан минимальный лимит для сделки"];
        }

        
        $p2p->max_limit = (float)Yii::$app->request->post("max_limit");
        if(!$p2p->max_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан максимальный лимит для сделки"];
        }

        $p2p->chart_id = Yii::$app->request->post("chart_id");
        if(!$p2p->chart_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан тип криптовалюты"];
        }

        $p2p->duration = (float)Yii::$app->request->post("duration") * 60;
        if(!$p2p->duration) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан срок действия сделки"];
        }
        
        $p2p->type = 2; //sell
        


        $chart = Chart::findOne(["id" => $p2p->chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        if ($chart->p2p == 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не входит в список доступных для p2p"];
        }
        
        //$paymentsIDs = [];
        //$paymentIDs = array();
        $payments = (array)Yii::$app->request->post("payments");
        //$paymentsIDs = explode(",", $payments);
        if(!$payments) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Выберите способы оплаты"];
        } 

        if(count($payments) > 10 ) {
        Yii::$app->response->statusCode = 400;
        return ["success" => false, "message" => "Превышено количество способов оплаты в рамках одного ордера"];
        }
        
        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" =>$chart->id]); //
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0]);
            return ["success" => false, "message" => "Необходимо пополнить кошелек перед созданием ордера на продажу"];
        }

        if ($p2p->currency_id == 1) {
            if ($p2p->min_limit < 500) {
            return ["success" => false, "message" => "Минимальное количество должно быть больше 500 руб"];
            }
        }
        if ($p2p->max_limit / $p2p->course > $wallet->balance) {
        return ["success" => false, "message" => "Превышен лимит текущего баланса"];
        }
        if ($p2p->max_limit / $p2p->course > $p2p->amount) {
        return ["success" => false, "message" => "Превышен максимальный лимит суммы ордера"];
        }
        if ($p2p->max_limit < $p2p->min_limit) {
        return ["success" => false, "message" => "Минимальный лимит меньше максимального"];
        }

        // if ($p2p->min_limit / $p2p->course > $p2p->amount) {
        // return ["success" => false, "message" => "Минимальный лимит меньше суммы предложения"];
        // }
        


        if($p2p->type == 2) {
            $wallet->balance -= $p2p->amount; //резервирование средств с финансового кошелька для продажи криптовалюты
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $p2p->wallet_id = $wallet->id;
        $p2p->uuid = rand(100000000,999999999);
        $p2p->status = $status;


        



        $data = [];
        
        if(!$p2p->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }
        
        $data['id'] = $p2p->id;
        $data['uuid'] = $p2p->uuid;
         
        
        foreach ($payments as $paymentsss) {
            $p2p_payment = new P2pPayment(["p2p_ads_id" => $p2p->id, "payment_id" => $paymentsss, "user_id" => $this->user->id]);
            $p2p_payment_user = PaymentUser::find()->where(['user_id' => $this->user->id, 'payment_id' => $paymentsss, 'active'=>1])->one();
            if(!$p2p_payment->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения способа оплаты"];
            }
            if (!$p2p_payment_user) {
                return ["success" => false, "message" => "Вы не заполнили реквизиты для выбранного метода оплаты"];
            } else {
                $p2p_payment->save();
            }
        }
        
       

        return ["success" => true, "message" => "Объявление о продаже криптовалюты успешно опубликовано", "data" => $data, "wallet" => $wallet];
     }


     /**
     * @SWG\Post(
     *    path = "/p2p/create-buy",
     *    tags = {"p2p"},
     *    summary = "Создать обьявление о покупке криптовалюты на p2p (BUY)",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="ID криптовалюты",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="body",
     *      description="Валюта (по умолчанию рубль)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="amount",
     *      in="body",
     *      description="Сумма ордера (в криптовалюте)",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="course",
     *      in="body",
     *      description="Курс",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="min_limit",
     *      in="body",
     *      description="Минимальное количество (min_limit) (в валюте)",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="max_limit",
     *      in="body",
     *      description="Максимальное количество (max_limit) (в валюте)",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="payments",
     *      in="body",
     *      description="Тип оплаты",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="duration",
     *      in="body",
     *      description="Время действия (в минутах)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="условия сделки",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Создание ордера p2p на покупку (статус Создан (PENDING)",
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
    public function actionCreateBuy()
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

        $p2p = new P2pAds(["date" => time(), "user_id" => $this->user->id]);
        $p2p->currency_id = Yii::$app->request->post("currency_id", 1);
        $p2p->description = Yii::$app->request->post("description", 'стандартные условия');
        
        $p2p->amount = (float)Yii::$app->request->post("amount"); //количество для покупки
        $p2p->start_amount = $p2p->amount;
        if(!$p2p->amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указано количество"];
        }


        $p2p->course = (float)Yii::$app->request->post("course"); //курс
        if(!$p2p->course) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан курс"];
        }

        $p2p->min_limit = (float)Yii::$app->request->post("min_limit");
        if(!$p2p->min_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан минимальный лимит для сделки"];
        }

        
        $p2p->max_limit = (float)Yii::$app->request->post("max_limit");
        if(!$p2p->max_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан максимальный лимит для сделки"];
        }

        $p2p->chart_id = Yii::$app->request->post("chart_id");
        if(!$p2p->chart_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан тип криптовалюты"];
        }

        $p2p->duration = (float)Yii::$app->request->post("duration") * 60;
        if(!$p2p->duration) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан срок действия сделки"];
        }
        
        $p2p->type = 1; //buy
        $status = -1;  //создать ордер


        $chart = Chart::findOne(["id" => $p2p->chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        if ($chart->p2p == 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не входит в список доступных для p2p"];
        }
        
        //$paymentsIDs = [];
        //$paymentIDs = array();
        $payments = (array)Yii::$app->request->post("payments");
        //$paymentsIDs = explode(",", $payments);
        if(!$payments) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Выберите способы оплаты", "payments"=>$payments];
        } 

        


        if(count($payments) > 10 ) {
        Yii::$app->response->statusCode = 400;
        return ["success" => false, "message" => "Превышено количество способов оплаты в рамках одного ордера"];
        }
        
        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" =>$chart->id]); //
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0]);
            //return ["success" => false, "message" => "Необходимо пополнить кошелек перед созданием ордера на продажу"];
        }

        if ($p2p->currency_id == 1) {
            if ($p2p->min_limit < 500) {
            return ["success" => false, "message" => "Минимальное количество должно быть больше 500 руб"];
            }
        }
    //  if ($p2p->max_limit > $wallet->balance || $p2p->max_limit < $p2p->amount) {
    //     return ["success" => false, "message" => "Превышен лимит текущего баланса"];
    //  }
        if ($p2p->max_limit < $p2p->min_limit) {
        return ["success" => false, "message" => "Минимальный лимит меньше максимального"];
        }

        // if ($p2p->min_limit / $p2p->course > $p2p->amount) {
        // return ["success" => false, "message" => "Минимальный лимит меньше суммы предложения"];
        // }
        


    //  if($p2p->type == 1) {
    //      $wallet->balance -= $p2p->amount; //резервирование средств с финансового кошелька для продажи криптовалюты
    //  }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $p2p->wallet_id = $wallet->id;
        $p2p->uuid = rand(100000000,999999999);
        $p2p->status = $status;


        


        $data = [];
        if(!$p2p->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }


        foreach ($payments as $paymentsss) {
            $p2p_payment = new P2pPayment(["p2p_ads_id" => $p2p->id, "payment_id" => $paymentsss, "user_id" => $this->user->id]);
            $p2p_payment_user = PaymentUser::find()->where(['user_id' => $this->user->id, 'payment_id' => $paymentsss, 'active'=>1])->one();
            $p2p_payment->save();
            if (!$p2p_payment_user) {
                return ["success" => false, "message" => "Вы не заполнили реквизиты для выбранного метода оплаты"];
            } else {
                $p2p_payment->save();
            }
        }


        
        
        $data['id'] = $p2p->id;
        $data['uuid'] = $p2p->uuid;


        return ["success" => true, "message" => "Объявление о покупке криптовалюты успешно опубликовано", "data" => $data];
    }
   



    /**
     * @SWG\Post(
     *    path = "/p2p/edit-order",
     *    tags = {"p2p"},
     *    summary = "Редактирование обьявления автором",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      required=true,
     *      description="p2p_ads_id ордера",
     *      @SWG\Schema(type="integer")
     
     *     ),
     *    @SWG\Parameter(
     *      name="uuid",
     *      in="body",
     *      description="uuid ордера",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="body",
     *      description="Валюта (по умолчанию рубль)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="body",
     *      description="Криптовалюта",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="course",
     *      in="body",
     *      description="Курс",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="min_limit",
     *      in="body",
     *      description="Минимальный лимит",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="max_limit",
     *      in="body",
     *      description="Максимальное лимит",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="payments",
     *      in="body",
     *      description="Тип оплаты",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="условия сделки",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Ордер успешно отредактирован",
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
   
    public function actionEditOrder()
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

        $p2p_ads_id = Yii::$app->request->post("p2p_ads_id");

        $p2pAds_history = P2pHistory::find()->where(["p2p_ads_id" => $p2p_ads_id])->all();

        if($p2pAds_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "По ордеру есть история, не может быть отредактирован"];
        }

        if(!$p2p_ads_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан p2p_ads_id ордера"];
        }
        $result = P2pAds::find()->where(["id" => $p2p_ads_id, "status" => -1])->one();
   
        if (!$result) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не найден"];
        }


        if ($result->amount != $result->start_amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть отредактирован так как по нему имеются заверешенные сделки, создайте новый ордер или удалите текущий"];
        }

        if($result->status != -1) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть отредактированб либо он отменен системой"];
        }

        

        $chart_id = Yii::$app->request->post("chart_id");
        if(!$chart_id) {
            $chart_id = $result->chart_id;
        }

        

        $currency_id = Yii::$app->request->post("currency_id");
        if(!$currency_id) {
            $currency_id = $result->currency_id;
        }
        
        

        $amount = (float)Yii::$app->request->post("amount");
        if(!$amount) {
            $amount = $result->amount;

        }
        
        $current_wallet = Wallet::find()->where(["user_id" => $result->user_id, "chart_id" => $chart_id])->one();

        if (!$current_wallet) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не достаточно средств на балансе или кошелек не найден, удалите ордер или создайте другой"];
        }

        if ($amount > $current_wallet->balance) {

            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Сумма ордера превышает текущий баланс кошелька"];

        }



        $min_limit = (float)Yii::$app->request->post("min_limit");
        if(!$min_limit) {
            $min_limit = $result->min_limit;
        }
        $max_limit = (float)Yii::$app->request->post("max_limit");
        if(!$max_limit) {
            $max_limit = $result->max_limit;
        }
        $course = (float)Yii::$app->request->post("course");
        if(!$course) {
            $course = $result->course;
        }
        $payments = (array)Yii::$app->request->post("payments");
        //$paymentsIDs = [];
        //$paymentIDs = array();
        
        //$paymentsIDs = explode(",", $payments);
        if(!$payments) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Выберите способы оплаты"];
        }
        if(count($payments) > 10 ) {
        Yii::$app->response->statusCode = 400;
        return ["success" => false, "message" => "Превышено максимальное количество способов оплаты в рамках одного ордера"];
        }
        
        $p2p_payment_delete = P2pPayment::find()->where(["user_id" => $this->user->id,'p2p_ads_id'=>$result->id])->all();
        if(count($p2p_payment_delete) > 10 ) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Превышено максимальное количество способов оплаты в рамках одного ордера"];
            }
        foreach ($p2p_payment_delete as $fordelete) {
            $fordelete->delete();
        }
        foreach ($payments as $payment) {
           
            $p2p_payment = new P2pPayment(["p2p_ads_id" => $result->id, "payment_id" => $payment, "user_id" => $this->user->id]);
            //проверить наличие уже в 
            $p2p_payment->save();
        }



        $duration = Yii::$app->request->post("duration") * 60;
        if(!$duration) {
            $duration = $result->duration;
        }
        
        $data = [];
        
        

        if ($result){
            $result->currency_id = $currency_id;
            $result->amount = $amount;
            $result->start_amount = $amount;
            $result->min_limit = $min_limit;
            $result->max_limit = $max_limit;
            $result->course = $course;
            $result->duration = $duration;
            $result->description = Yii::$app->request->post("description", $result->description);
            $result->status = -1; //PENDING again
            $result->date = time();
            $result->save();
            return ["success" => true, "message" => "Ордер успешно изменен"];

        }
     

        
    }

    
    /**
     * @SWG\Delete(
     *    path = "/p2p/remove-order",
     *    tags = {"p2p"},
     *    summary = "Удаление обьявления после отмены",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="p2p_ads_id ордера",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="uuid",
     *      in="body",
     *      description="uuid ордера",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Удаление ордера p2p(статус удален (REMOVED)",
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
    public function actionRemoveOrder()
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

        $p2p_ads_id = Yii::$app->request->post("p2p_ads_id");
        if (!$p2p_ads_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан ID ордера"];
        }
        
        $p2p_ads_history = P2pHistory::find()->where(['p2p_ads_id' => $p2p_ads_id, "creator_id" => $this->user->id, 'status' => 2])->all();
        if ($p2p_ads_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть удален, имеются оплаченые но не завершенные сделки по нему"];
        }

        $result = P2pAds::find()->where(["id" => $p2p_ads_id, "user_id" => $this->user->id, "status" => 6])->one();
        if(!$result) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть удален, отмените остатки перед удалением", $result];
        }
        
        // $current_wallet = Wallet::find()->where(["user_id" => $this->user->id, "chart_id" => $result->chart_id])->one();
        // if(!$current_wallet) {
        //     Yii::$app->response->statusCode = 401;
        //     return ["success" => false, "message" => "Кошелек пользователя не найден, обратитесь к администратору"];
        // }
        $result->status = 9;
        if ($result->save()) {
            return ["success" => true, "message" => "Ордер успешно удален"];
        }
                   
            
         
    }


         
    /**
     * @SWG\Get(
     *    path = "/p2p/full-list",
     *    tags = {"p2p"},
     *    summary = "Список всех ордеров + фильтр по uuid + фильтр по параметрам",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="uuid",
     *      in="path",
     *      description="uuid Сделки",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="path",
     *      description="2 - продажа, 1 - покупка, по умолчанию - все",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="user_id",
     *      in="path",
     *      description="ID пользователя (без указания, только свои сделки)",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="amount",
     *      in="path",
     *      description="фильтр по сумме (в рублях)",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="currency_id",
     *      in="path",
     *      description="Тип валюты,  по умолчанию - рубль",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="chart_id",
     *      in="path",
     *      description="Тип криптовалюты,  по умолчанию - все",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="duration",
     *      in="path",
     *      description="Период исполнения ордера (в минутах), без указания все",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="payments",
     *      in="path",
     *      description="Способы оплаты,  по умолчанию - все",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="author_id",
     *      in="path",
     *      description="История ордеров по author_id в history",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="creator_id",
     *      in="path",
     *      description="История ордеров по creator_id в history",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="status",
     *      in="path",
     *      description="Статус ордера, если не указаны то все статусы, для активных ордеров (-1)",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="status_history",
     *      in="path",
     *      description="Статус ордера из истории",
     *      type="integer",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     @SWG\Schema(ref = "#/definitions/P2pAds")
     *     ),
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
   
    public function actionFullList()
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

        //$typeall = array(0,1);
        $p2p_ads_id = Yii::$app->request->get("uuid");
        if(!$p2p_ads_id) {
            $whereid = ["IS NOT","p2p_ads.uuid", null];
        } else {
            $whereid = ["p2p_ads.uuid" => $p2p_ads_id]; 
        }
        $all_orders = 0;
        $author_id = Yii::$app->request->get("author_id");

        if(!$author_id) {
             $all_orders = 1;
        }
        
        // else {
        //     $whereid = ["p2p_ads.uuid" => $p2p_ads_id]; 
        // }

        

        $userIDs = array();
        $users = Yii::$app->request->get("user_id");
        $userIDs = explode(",", $users);
        if(!$users) {
            $whereusers = ["<>", "p2p_ads.user_id", $this->user->id];
        } else {
            $whereusers = ["in", "p2p_ads.user_id", $userIDs];
        }

        $statusIDs = array();
        $statuses = Yii::$app->request->get("status");
        $statusIDs = explode(",", $statuses);
        if(!$statuses) {
            $wherestatus = ["IS NOT", "p2p_ads.status", null];
        } else {
            $wherestatus = ["in", "p2p_ads.status", $statusIDs];
        }

        $statushIDs = array();
        $statusesh = Yii::$app->request->get("status_history");
        $statushIDs = explode(",", $statusesh);
        if(!$statusesh) {
            $wherestatush = ["IS NOT", "status", null];
        } else {
            $wherestatush = ["in", "status", $statushIDs];
        }


        $type = Yii::$app->request->get("type");
        
        if(!$type) {
            $wheretype = ["IS NOT","p2p_ads.type", null];
        } else {
            $wheretype = ["p2p_ads.type" => $type]; 
            
        }

        $summ = (float)Yii::$app->request->get("amount");
        
        if(!$summ) {
            $wheresummmin = ["IS NOT","min_limit", null];
            $wheresummmax = ["IS NOT","max_limit", null];
        } else {
            $wheresummmin =  ['<=','min_limit', $summ]; 
            $wheresummmax =  ['>=','max_limit', $summ]; 
            
        }

        $chartIDs = array();
        $charts = Yii::$app->request->get("chart_id");
        $chartIDs = explode(",", $charts);
        if(!$charts) {
            $wherechart = ["chart.active" => 1];
        } else {
            $wherechart = ["in","chart.id", $chartIDs];

        }

        $currencyIDs = array();
        $currencys = Yii::$app->request->get("currency_id");
        $currencyIDs = explode(",", $currencys);
        if(!$currencys) {
            $wherecurrency = ["currency.active" => 1];
        } else {
            $wherecurrency = ["in","currency.id", $currencyIDs];

        }

        $duration = (int)Yii::$app->request->get("duration");
        if(!$duration) {
            $whereduration = ["IS NOT","p2p_ads.duration", null];
        } else {
            $whereduration = ["p2p_ads.duration" => $duration * 60];

        }
        $paymentsIDs = array();
        $paymentsss = Yii::$app->request->get("payments");
        $paymentsIDs = explode(",", $paymentsss);
        if(!$paymentsss) {
                $wherepayments = ["payment_type.active" => 1];
            } else {
                $wherepayments = ["in","payment_type.id", $paymentsIDs];
            }
        

                
        $data = [];
        $p2pAds_query = P2pAds::find()->joinwith(['chart','currency'])
        ->where($whereid)
        ->andwhere($wheretype)
        ->andwhere($wherechart)
        ->andwhere($wherecurrency)
        ->andwhere($wherestatus)
        ->andWhere($whereusers)
        ->andWhere($whereduration)
        ->andWhere($wheresummmin)
        ->andWhere($wheresummmax)
        ->all();

        foreach ($p2pAds_query as $item)
        {
            $p2pAds_query_count = P2pAds::find()->where(["user_id" => $item->user->id])->count();
            $p2pAds_query_count_complete = P2pAds::find()->where(["user_id" => $item->user->id, "status" => 10])->count();
            $complete = number_format($p2pAds_query_count_complete / $p2pAds_query_count, 2, '.','');
            $p2pAds_history = P2pHistory::find()->where(["creator_id" => $item->user->id, "p2p_ads_id" => $item->id])->andwhere($wherestatush)->all();
            
            if (!$p2pAds_history) {
                
                $historys = null;
                if ($statusesh) {
                    continue;
                }
                
            } else {
            $historys = [];
            if (!$author_id) {
                //если не указан author_id
                foreach ($p2pAds_history as $history) {
                    if ($history->price > 1) {
                        $history->price = number_format($history->price, 2,'.','');
                    }
                    else {
                        $history->price = number_format($history->price, 10,'.','');
                        $history->price = rtrim($history->price, '0');
                    }
                    
                    $history_payment = PaymentUser::find()->joinwith(['type'])->where(['user_id' => $history->creator_id, "payment_id" => $history->payment_id])->one();                    
                    
                    $historys[]=[
                        "order_id" => $history->p2p_ads_id,
                        "volume" => (float)$history->price,
                        "start_date" => date("Y-m-d H:i:s", $history->start_date),
                        "end_date" => date("Y-m-d H:i:s", $history->end_date),
                        "payment_id" => $history_payment->payment_id ?? 'не указано',                        
                        "name" => $history_payment->type->name ?? 'не указано',
                        "value" => $history_payment->value ?? 'не указано',
                        "payment_receiver" => $history_payment->payment_receiver ?? 'не указано',
                        "author_id" => $history->author_id,
                        "creator_id" => $history->creator_id,
                        "status_history" => $history->status,
                    ];

                    }
                } else {
                    $p2pAds_history = P2pHistory::find()->where(["author_id" => $author_id, "p2p_ads_id" => $item->id])->andwhere($wherestatush)->all();
                    if (!$p2pAds_history) {
                    continue;
                    } else {
                        foreach ($p2pAds_history as $history) {
                            if ($history->price > 1) {
                                $history->price = number_format($history->price, 2,'.','');
                            }
                            else {
                                $history->price = number_format($history->price, 10,'.','');
                                $history->price = rtrim($history->price, '0');
                            }
                            
                            $history_payment = PaymentUser::find()->joinwith(['type'])->where(['user_id' => $history->creator_id, "payment_id" => $history->payment_id])->one();

                            $historys[]=[
                                "order_id" => $history->p2p_ads_id,
                                "volume" => (float)$history->price,
                                "start_date" => date("Y-m-d H:i:s", $history->start_date),
                                "end_date" => date("Y-m-d H:i:s", $history->end_date),
                                "payment_id" => $history_payment->payment_id ?? 'не указано',                        
                                "name" => $history_payment->type->name ?? 'не указано',
                                "value" => $history_payment->value ?? 'не указано',
                                "payment_receiver" => $history_payment->payment_receiver ?? 'не указано',
                                "author_id" => $history->author_id,
                                "creator_id" => $history->creator_id,
                                "status_history" => $history->status,
                            ];
                        }
                    }
                }
            }
            

            
            
            $p2pAds_payments = P2pPayment::find()->joinwith(['paymentType','paymentUser'])
            ->where(["p2p_ads_id" => $item->id])
            ->andwhere($wherepayments)
            ->all();
            if (!$p2pAds_payments) {
            continue;
            } else {
            
                $payments = [];
                foreach ($p2pAds_payments as $payment) {
                    $p2p_reqs = PaymentUser::find()->where(['user_id' => $item->user->id, "payment_id" => $payment->paymentType->id])->one();
                    if(!$p2p_reqs) {
                        $payments = null;
                        continue;
                        // Yii::$app->response->statusCode = 401;
                        // return ["success" => false, "message" => "Выбранные способы валюты не найдены, ордер оформлен не полностью", $payment->paymentType->id];
                    } else {
                        $payments[] = [
                            "id" => $payment->paymentType->id ?? 'не указано',
                            "name" => $payment->paymentType->name ?? 'не указано',
                            "value" => $p2p_reqs["value"] ?? 'не указано',
                            "payment_receiver" => $p2p_reqs["payment_receiver"] ?? 'не указано'
                            
                        ];
                    }
                }
            }
            if ($payments == null) {
                continue;
            }
            $can_delete = 1; 
            // if ($item->start_amount<$item->amount) {
            //     $can_delete = 0;
            // }
            if ($item->status == 5 || $item->status == 7 || $item->status == 2) {
            $can_delete = 0;
            }
            
	        if ($item->amount > 1) {
                $item->amount = number_format($item->amount, 2,'.','');
            }
            else {
                $item->amount = number_format($item->amount, 10,'.','');
                $item->amount = (float)rtrim($item->amount, '0');
            }
            if ($item->amount == 0) {
                $item->amount = 0;
            }

            if ($item->min_limit * $item->course > 1) {
                $item->min_limit = number_format($item->min_limit, 2, '.', '');
            }
            

            if ($item->max_limit * $item->course > 1) {
                $item->max_limit = number_format($item->max_limit, 2, '.', '');

            }
          
            
            if ($item->course > 1) {
                $item->course = number_format($item->course, 2, '.','');
            }


            $data[] = [
                "order_id" => $item->id,
                "uuid" => (int)$item->uuid,
	            "date" => date("Y-m-d H:i:s", $item->date),
                "user_id" => $item->user->id,
                "user" => $item->user->login,
                "first_name" => $item->user->first_name,
                "last_name" => $item->user->last_name,
                "patronymic" => $item->user->patronymic,
                "verify_status" => $item->user->verify_status,
                "type" => $item->type,
                "chart_id" => $item->chart->id,
                "chart" => $item->chart->symbol,
                "currency" => $item->currency->symbol,
                "currency_id" => $item->currency_id,
                "full_amount" => (float)$item->start_amount,
                "amount" => (float)$item->amount,
                "course" => (float)$item->course,
                "min_limit" => (float)$item->min_limit,
                "max_limit" => (float)$item->max_limit,
                "duration" => $item->duration / 60,
                "payments" => $payments,
                "history" => $historys,
                "count_payments_order_type" => count($p2pAds_payments),
                "status" => $item->status,
                "can_delete" => $can_delete,
                "image" => Url::to([$item->user->getImage()->getUrl("75x75")], "https"),
                "user_orders_count" => (int)$p2pAds_query_count,
                "user_orders_count_complete_percent" =>(int)$complete,
                'description' => $item->description
                
            ];
        }
        
        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/p2p/confirm-trade",
     *    tags = {"p2p"},
     *    summary = "Подтверждение участия в сделке",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="offer",
     *      in="body",
     *      description="Сумма (в currency)",
     *      required=true,
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="payment",
     *      in="body",
     *      description="Способ оплаты",
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Подтверждено участие в ордере",
     *      @SWG\Schema(ref = "#/definitions/P2pHistory")
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
    public function actionConfirmTrade()
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


        $p2p_ads_id = Yii::$app->request->post("p2p_ads_id");
        
        
        $p2p_ads = P2pAds::find()->where(["id" => $p2p_ads_id])->andWhere(['status' => -1])->one();

        

        if(!$p2p_ads) {
            Yii::$app->response->statusCode = 401;

            return ["success" => false, "message" => "Ордер не найден"];
        }

        if ($p2p_ads->currency_id == 1) {
            if((float)Yii::$app->request->post("offer") < 500) {
                    Yii::$app->response->statusCode = 401;
                    return ["success" => false, "message" => "Сумма должна быть больше 500 р."];
                }
        }

        //payment не нужен когда type BUY ПОКУПКА 1
        $p2p_payment = (int)Yii::$app->request->post("payment");
        if(!$p2p_payment && $p2p_ads->type == 1) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не выбран способ оплаты для продажи криптовалюты"];
            
        }

        $p2pAds_history = P2pHistory::find()->where(["p2p_ads_id" => $p2p_ads_id, "author_id" => $this->user->id, "status" => [1,2]])->one();
        if($p2pAds_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "У вас есть активный ордер с текущим пользователем"];
        }

        if ($p2p_ads->type == 1) {
            $payments_seller = P2pPayment::find()->where(['p2p_ads_id' => $p2p_ads->id])->all();
            foreach ($payments_seller as $payment_seller) {
                $flag = False;
                if ($p2p_payment == $payment_seller->payment_id) {
                    $flag = True;
                    break;
                }
            }
            if (!$flag) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Не соответствует способам оплаты в ордере покупателя"];
            }
        }


        $chart_name = Chart::find()->where(['id' => $p2p_ads->chart_id])->one();
        $currency_name = Currency::find()->where(['id' => $p2p_ads->currency_id])->one();


        //для входа в сделку на покупку крипта у author_id

        if ($p2p_ads->type == 1) {
            $seller_offer = (float)Yii::$app->request->post("offer"); 
            $wallet_seller = Wallet::find()->where(['user_id' => $this->user->id, 'chart_id' => $p2p_ads->chart_id])->one(); //наличие chart на кошельке чувака
            if (!$wallet_seller) {
        
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Отсутствует кошелек"];    
                }
            if ($wallet_seller->balance < 0) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Не достаточно средств для осуществления операции"];    
                }
            if ($wallet_seller->balance < $seller_offer / $p2p_ads->course) { //крипта покупателя

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Недостаточно средств на балансе"];    
                }
            $wallet_seller->balance -= $seller_offer / $p2p_ads->course;
            $status = 1; // торгуется (в течении срока оплаты)
            $p2p_h = new P2pHistory(["start_date" => time(), "author_id" => $this->user->id, "creator_id" => $p2p_ads->user_id, "status" => $status]);

            $trade_time = $p2p_ads->duration; //срок оплаты

            if ($trade_time == 900) {
                $duration = "+15 minutes";
            }
            if ($trade_time == 1800) {
                $duration = "+30 minutes";
            }

            $p2p_h->end_date = strtotime($duration, $p2p_h->start_date); //
            $p2p_h->p2p_ads_id = Yii::$app->request->post("p2p_ads_id");
            $p2p_h->price = $seller_offer / $p2p_ads->course; //в крипте
            
            if($p2p_ads->min_limit > $p2p_h->price * $p2p_ads->course) {
                Yii::$app->response->statusCode = 400;
                $min_author_price = $p2p_ads->min_limit / $p2p_ads->course;
                if ($min_author_price > 1) {
                    $min_author_price = number_format($min_author_price, 2,'.','');
                }
                else {
                    $min_author_price = number_format($min_author_price, 10,'.','');
                    $min_author_price = rtrim($min_author_price, '0');
                }
                return ["success" => false, "message" => "Минимальная сумма " . $min_author_price . " " .$chart_name->symbol . ", указано: " . $p2p_h->price . " " .$chart_name->symbol];
            }
    
            $max_limit = $p2p_ads->amount * $p2p_ads->course;
            
            if($p2p_h->price > $p2p_ads->max_limit / $p2p_ads->course) {
                Yii::$app->response->statusCode = 400;
                $max_author_price = $p2p_ads->max_limit / $p2p_ads->course;
                if ($max_author_price > 1) {
                    $max_author_price = number_format($max_author_price, 2,'.','');
                }
                else {
                    $max_author_price = number_format($max_author_price, 10,'.','');
                    $max_author_price = rtrim($max_author_price, '0');
                }
                
                return ["success" => false, "message" => "Максимальная сумма " . $max_author_price . " " . $chart_name->symbol . ", указано: " . $p2p_h->price . " " .$chart_name->symbol];
            }

            $ostatok = $p2p_ads->amount;
            $p2p_ads->amount -= $p2p_h->price;


            if ($p2p_ads->amount < 0) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сумма ордера больше максимального остатка", $ostatok. " " . $chart_name->symbol . " (" . $ostatok * $p2p_ads->course . " " . $currency_name->name . ")"];
            }

            //$p2p_ads->status = $status;
            $p2p_h->payment_id = Yii::$app->request->post("payment");

            if(!$p2p_ads->save()) {
                
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения объявления"];
            }

            if(!$p2p_h->save()) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения сделки (в истории)"];
            }
            if(!$wallet_seller->save()) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


            $data = [
                "id" => $p2p_h->p2p_ads_id,
                "offer" => (float)$p2p_h->price,
                "payment" => $p2p_h->payment_id,

            ];

            
            $user = User::find()->where(['id' => $p2p_ads->user_id])->one();
            if ($user) {
                $this->sendCode($user, $p2p_h->id);
            }

            return ["success" => true, "message" => "Подтверждено участие в сделке (покупка)", "data" => $data];

            
        }

        //sell
      
        $chart_name = Chart::find()->where(['id' => $p2p_ads->chart_id])->one();
        $status = 1; // торгуется (в течении срока оплаты)
        $p2p_h = new P2pHistory(["start_date" => time(), "author_id" => $this->user->id, "creator_id" => $p2p_ads->user_id, "status" => $status]);

        $trade_time = $p2p_ads->duration; //срок оплаты

        if ($trade_time == 900) {
            $duration = "+15 minutes";
        }
        if ($trade_time == 1800) {
            $duration = "+30 minutes";
        }
        if (!$trade_time) {
            $duration = "+15 minutes";
        }




        $p2p_h->end_date = strtotime($duration, $p2p_h->start_date); //
        $p2p_h->p2p_ads_id = Yii::$app->request->post("p2p_ads_id");
        $p2p_h->payment_id = 1000;
        $p2p_h->price = (float)Yii::$app->request->post("offer") / $p2p_ads->course; //оффер в рублях в базу в крипте
        //$author_price = $p2p_h->price / $p2p_ads->course;  //сумма покупателя * курс продавца бтс


        if($p2p_ads->min_limit > $p2p_h->price * $p2p_ads->course) {
            Yii::$app->response->statusCode = 400;
            $min_author_price = $p2p_ads->min_limit / $p2p_ads->course;
            if ($min_author_price > 1) {
                $min_author_price = number_format($min_author_price, 2,'.','');
            }
            else {
                $min_author_price = number_format($min_author_price, 10,'.','');
                $min_author_price = rtrim($min_author_price, '0');
            }
            return ["success" => false, "message" => "Минимальная сумма " . $min_author_price . " " .$chart_name->symbol. ", указано: " . $p2p_h->price. " " .$chart_name->symbol];
        }

        $max_limit = $p2p_ads->amount * $p2p_ads->course;
        
        if($p2p_h->price * $p2p_ads->course > $max_limit) {
            Yii::$app->response->statusCode = 400;
            $max_author_price = $p2p_ads->max_limit / $p2p_ads->course;
            if ($max_author_price > 1) {
                $max_author_price = number_format($max_author_price, 2,'.','');
            }
            else {
                $max_author_price = number_format($max_author_price, 10,'.','');
                $max_author_price = rtrim($max_author_price, '0');
            }
            
            return ["success" => false, "message" => "Максимальная сумма " . $max_author_price . " " . $chart_name->symbol];
        }

        $p2p_ads->amount -= $p2p_h->price;

        if ($p2p_ads->amount < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка! Сделка обновилась", $p2p_ads->amount];
        }

        //$p2p_ads->status = $status;

        if(!$p2p_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }

        if(!$p2p_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }


        $data = [
            "id" => $p2p_h->p2p_ads_id,
            "offer" => (float)$p2p_h->price
        ];

        $user = User::find()->where(['id' => $p2p_ads->user_id])->one();
        if ($user) {
            $this->sendCode($user, $p2p_h->id);
        }

        return ["success" => true, "message" => "Подтверждено участие в сделке (продажа)", "data" => $data];
        
    }


     /**
     * @SWG\Post(
     *    path = "/p2p/check-pay",
     *    tags = {"p2p"},
     *    summary = "Покупатель / продавец оплатил сделку",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads в history",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Покупатель / продавец оплатил сделку",
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
   
    public function actionCheckPay()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }
        
        $maycancel = [-1,6];

        $history_id = Yii::$app->request->post("p2p_ads_id");
        $p2p_ads = P2pAds::find()->where(['id' => $history_id, "status" => $maycancel])->one();
                        
        if (!$p2p_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена (основная)"];
        }
        if ($p2p_ads->type == 2) {
            $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'author_id' => $this->user->id, 'status' => 1])->one();
                }
        if ($p2p_ads->type == 1) {
                $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 1])->one();
                }
        
        
        
        if (!$p2p_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка в истории не найдена"];
        }



        if ((int)$p2p_h->end_date < time()) {
            if ($p2p_ads->type==2) {
                //$p2p_ads->status = 7;
                $p2p_h->status = 7;
                $p2p_ads->amount += $p2p_h->price;
                $p2p_h->price = 0;
                $p2p_ads->save();
                $p2p_h->save();
                //вернуть средства продавцу
                return ["success" => false, "message" => "Сделка просрочена, отменена системой"];
            }
            if ($p2p_ads->type==1) {
                $p2p_ads->status = 5;
                $p2p_h->status = 5;
                //$p2p_ads->amount += $p2p_h->price / $p2p_ads->course;
                $p2p_h->price = 0;
                $p2p_ads->save();
                $p2p_h->save();
                //аппеляция
                return ["success" => false, "message" => "Сделка просрочена,если вы оплатили средства обратитесь в техподдержку"];
            }

        }

        
        //$p2p_ads->status = 2; 
        $p2p_h->status = 2; //нажал кнопку платеж выполнен
        if (!$p2p_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Не удалось сохранить сделку"];
        }

        if (!$p2p_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ну удалось сохранить сделку в истории"];
        }


        return ["success" => true, "message" => "Сделка оплачена, ожидаем подтверждение"];
    
    }



    /**
     * @SWG\Post(
     *    path = "/p2p/cancel-order",
     *    tags = {"p2p"},
     *    summary = "Отмена ордера (или остатков)",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "отмена сделки",
     *      @SWG\Schema(ref = "#/definitions/P2pHistory")
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


     public function actionCancelOrder()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }
        

        
        $history_id = Yii::$app->request->post("p2p_ads_id");
        //$desc_id = Yii::$app->request->post("description_id", 6);
        $p2p_ads = P2pAds::find()->where(['id' => $history_id, 'user_id'=> $this->user->id])->one();
        // $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'author_id' => $this->user->id])->one();

        // if (!$p2p_h) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Сделка не найдена (история)"];
        // }
        if ($p2p_ads->status == 5) {
            return ["success" => false, "message" => "Ордер не может быть отменен, статус В аппеляции, обратитесь к администратору"];
        }
        if (!$p2p_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ордер не найден"];
        }
        if ($p2p_ads->status == -1) 
        {
                $p2p_ads->status = 6;
                //$p2p_h->status = 1;
                //$p2p_h->description_id = $desc_id;
                // if ($p2p_ads->type == 1) {
                //     $p2p_ads->amount += $p2p_h->price; //вернуть средства продавцу
                // }

                //$p2p_h->price = 0;
                
                if ($p2p_ads->type == 2) {
                    $wallet_seller = Wallet::find()->where(["user_id" => $p2p_ads->user_id, "chart_id" => $p2p_ads->chart_id])->one();
                    if (!$wallet_seller) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Невозможно пополнить баланс продавца"];
                    }
                    $wallet_seller->balance += $p2p_ads->amount; //вернуть средства (или остатки) продавцу на кошелек
                    $p2p_ads->amount = 0;
                    if(!$wallet_seller->save()) {
                        Yii::$app->response->statusCode = 400;
                        return ["success" => false, "message" => "Ошибка сохранения средств на кошельке"];
                    }

                }
//                if(!$p2p_h->save()) {
//                    Yii::$app->response->statusCode = 400;
//                    return ["success" => false, "message" => "Ошибка сохранения ордера"];
//                }
                if(!$p2p_ads->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения ордера"];
                }

                return ["success" => true, "message" => "Ордер отменен"];
        }


     
     }

    /**
     * @SWG\Post(
     *    path = "/p2p/cancel-trade",
     *    tags = {"p2p"},
     *    summary = "Отмена сделки",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description_id",
     *      in="body",
     *      description="ID причины отмены (по умолчанию 6 - другое)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "отмена сделки",
     *      @SWG\Schema(ref = "#/definitions/P2pHistory")
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


     public function actionCancelTrade()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }
        
        $maycancel = [1,2];

        
        $history_id = Yii::$app->request->post("p2p_ads_id");
        $desc_id = Yii::$app->request->post("description_id", 6);
        $p2p_ads = P2pAds::find()->where(['id' => $history_id])->one();
        if ($p2p_ads->type == 1) {
        $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'creator_id' => $this->user->id, "status"=>$maycancel])->one();
        } else {
        $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'author_id' => $this->user->id, "status"=>$maycancel])->one();
        }

        if (!$p2p_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Нельзя отменить ордер, вторая сторона оплачивает фиат, либо оповестите в чате об отмене"];
        }
        
        if ($p2p_h->status == 2) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Нельзя отменить, ордер уже оплачен"];
        }
        
        // if (!$p2p_h) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Сделка не найдена (история)"];
        // }

        // if (!$p2p_ads) {
        //     Yii::$app->response->statusCode = 400;
        //     return ["success" => false, "message" => "Сделка не найдена"];
        // }
        if ($p2p_ads->status == -1) 
        {
                //$p2p_ads->status = 6;
                $p2p_h->status = 6;
                $p2p_h->description_id = $desc_id;
                if ($p2p_ads->type == 1) {
                 $p2p_ads->amount += $p2p_h->price; //вернуть средства в ордер
                 $wallet_seller = Wallet::find()->where(['user_id' => $p2p_h->author_id, 'chart_id' => $p2p_ads->chart_id])->one();

                 if (!$wallet_seller) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Не удалось вернуть средства на кошелек"];
                 }
                 $wallet_seller->balance += $p2p_h->price;
                 if (!$wallet_seller->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Не удалось сохранить параметры кошелька"];
                 }


                }

                //$p2p_h->price = 0;
                
                if ($p2p_ads->type == 2) {
                    $p2p_ads->amount += $p2p_h->price; //вернуть средства в ордер
                }
                if(!$p2p_h->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения сделки"];
                }
                if(!$p2p_ads->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения сделки"];
                }

                return ["success" => true, "message" => "Сделка отменена"];
        }
    }




     /**
     * @SWG\Post(
     *    path = "/p2p/appeal",
     *    tags = {"p2p"},
     *    summary = "Аппеляция",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "отмена ордера",
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


     public function actionAppeal()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }

        $history_id = Yii::$app->request->post("history_id");

        $p2p_ads = P2pAds::find()->where(['id' => $history_id])->one();
        if (!$p2p_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена"];
        }
        if ($p2p_ads->type == 2) {
            $p2p_h = B2bHistory::find()->where(['p2p_ads_id' => $history_id, 'author_id' => $this->user->id])->one();
        } else {
            $p2p_h = B2bHistory::find()->where(['p2p_ads_id' => $history_id, 'creator_id' => $this->user->id])->one();
        }

        if (!$p2p_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена (в истории)"];
        }

        

        if ($p2p_h->status == 2) {
            $p2p_ads->status = 5;
            $p2p_h->status = 5;
            
        } else {
            return ["success" => false, "message" => "Сделка еще не оплачена"];
        }
        
        // $p2p_ads->amount += $p2p_h->price / $p2p_ads->course;
        // $p2p_h->price = 0;
        
        //разбор
    
        if(!$p2p_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }
        if(!$p2p_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }

        return ["success" => true, "message" => "Сделка в аппеляции, рассматривается администратором"];

    }

    /**
     * @SWG\Post(
     *    path = "/p2p/confirm-payment",
     *    tags = {"p2p"},
     *    summary = "Подтверждение оплаты продавцом / покупателем",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="p2p_ads_id",
     *      in="body",
     *      description="id ордера p2p_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "подтверждение оплаты продавцом / покупателем",
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

     public function actionConfirmPayment()
     {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }

        $history_id = Yii::$app->request->post("p2p_ads_id");
        $p2p_ads = P2pAds::find()->where(['id' => $history_id, 'status' => [-1,6]])->one();
        $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 2])->one();

        if (!$p2p_ads) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не найден"];
        }

        if ($p2p_ads->type == 2) {
            $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 2])->one();
            if (!$p2p_h) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сделка не найдена (в истории)"];
            }
            //подтвердил оплату 
            if($p2p_ads->amount == 0) {
                $p2p_ads->status = 10;
            } else {
                $p2p_ads->status = -1;
                if ($p2p_ads->min_limit / $p2p_ads->course > $p2p_ads->amount) {
                    $p2p_ads->min_limit = $p2p_ads->amount * $p2p_ads->course;
                }
            }
            $p2p_h->status = 4;
            $wallet_seller = Wallet::findOne(["user_id" => $p2p_h->author_id, 'chart_id' => $p2p_ads->chart_id]);
            if (!$wallet_seller) {
                $wallet_seller = new Wallet(["user_id" => $p2p_h->author_id, "chart_id" => $p2p_ads->chart_id, "type" => 0]);
            }
            $wallet_seller->balance += $p2p_h->price;
            if(!$wallet_seller->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


        }



        if ($p2p_ads->type == 1) {
            $p2p_h = P2pHistory::find()->where(['p2p_ads_id' => $history_id, 'author_id' => $this->user->id, 'status' => 2])->one();
            if (!$p2p_h) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сделка не найдена (в истории)"];
            }
            //подтвердил оплату 
            if($p2p_ads->amount == 0) {
                $p2p_ads->status = 10;
            } else {
                $p2p_ads->status = -1;
                if ($p2p_ads->min_limit / $p2p_ads->course > $p2p_ads->amount) {
                    $p2p_ads->min_limit = $p2p_ads->amount * $p2p_ads->course;
                }
            }
            $p2p_h->status = 4;
            $wallet_buyer = Wallet::findOne(["user_id" => $p2p_h->creator_id, 'chart_id' => $p2p_ads->chart_id]);
            if (!$wallet_buyer) {
                $wallet_buyer = new Wallet(["user_id" => $p2p_h->creator_id, "chart_id" => $p2p_ads->chart_id]);
            }
            $wallet_buyer->balance += $p2p_h->price;
            if(!$wallet_buyer->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


        }
        if(!$p2p_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }
        if(!$p2p_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки (в истории)"];
        }
        

        return ["success" => true, "message" => "Сделка подтверждена"];
     
     
    }

 /**
     * @SWG\Get(
     *    path = "/p2p/history",
     *    tags = {"p2p"},
     *    summary = "История ордеров",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      description="id ордера",
     *      type="integer",
     *     ),

     *    @SWG\Parameter(
     *      name="user_id",
     *      in="path",
     *      description="ID пользователя",
     *      type="integer",
     *     ),

     *    @SWG\Parameter(
     *      name="status_history",
     *      in="path",
     *      description="Статус ордера из истории",
     *      type="integer",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     @SWG\Schema(ref = "#/definitions/P2pHistory")
     *     ),
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

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if (!in_array($this->user->verify_status, self::VERIFY_STATUS))
        {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вам необходимо пройти полную верификацию для осуществления данной операции"];
        }

        //$typeall = array(0,1);
        $p2p_ads_id = Yii::$app->request->get("id");
        if(!$p2p_ads_id) {
            $whereid = ["IS NOT","p2p_ads.id", null];
        } else {
            $whereid = ["p2p_ads.id" => $p2p_ads_id]; 
        }
        $all_orders = 0;
        $author_id = Yii::$app->request->get("author_id");
        
        if(!$author_id) {
             $all_orders = 1;
        }
        // else {
        //     $whereid = ["p2p_ads.uuid" => $p2p_ads_id]; 
        // }

        

        $userIDs = array();
        $users = Yii::$app->request->get("user_id");
        $userIDs = explode(",", $users);
        if(!$users) {
            $whereusers = ["IS NOT","p2p_ads.id", null];
        } else {
            $whereusers = ["in", "p2p_history.author_id", $userIDs];
        }

        $statusIDs = array();
        $statuses = Yii::$app->request->get("status");
        $statusIDs = explode(",", $statuses);
        if(!$statuses) {
            $wherestatus = ["IS NOT", "p2p_ads.status", null];
        } else {
            $wherestatus = ["in", "p2p_ads.status", $statusIDs];
        }

        $statushIDs = array();
        $statusesh = Yii::$app->request->get("status_history");
        $statushIDs = explode(",", $statusesh);
        if(!$statusesh) {
            $wherestatush = ["IS NOT", "p2p_history.status", null];
        } else {
            $wherestatush = ["in", "p2p_history.status", $statushIDs];
        }


        $type = Yii::$app->request->get("type");
        
        if(!$type) {
            $wheretype = ["IS NOT","p2p_ads.type", null];
        } else {
            $wheretype = ["p2p_ads.type" => $type]; 
            
        }

        $chartIDs = array();
        $charts = Yii::$app->request->get("chart_id");
        $chartIDs = explode(",", $charts);
        if(!$charts) {
            $wherechart = ["chart.active" => 1];
        } else {
            $wherechart = ["in","chart.id", $chartIDs];

        }

        $currencyIDs = array();
        $currencys = Yii::$app->request->get("currency_id");
        $currencyIDs = explode(",", $currencys);
        if(!$currencys) {
            $wherecurrency = ["currency.active" => 1];
        } else {
            $wherecurrency = ["in","currency.id", $currencyIDs];

        }

        $duration = (int)Yii::$app->request->get("duration");
        if(!$duration) {
            $whereduration = ["IS NOT","p2p_ads.duration", null];
        } else {
            $whereduration = ["p2p_ads.duration" => $duration * 60];

        }

        
        $paymentsIDs = array();
        $paymentsss = Yii::$app->request->get("payments");
        $paymentsIDs = explode(",", $paymentsss);
        if(!$paymentsss) {
            $wherepayments = ["payment_type.active" => 1];
        } else {
            $wherepayments = ["in","payment_type.id", $paymentsIDs];
        }

                
        $data = [];
        $p2pAds_query = P2pHistory::find()->joinwith(['ads'])
        ->where($whereid)
        //->where($whereusers)
        ->andwhere($wherestatush)
        ->all();
        
        foreach ($p2pAds_query as $item)
            {   
                $historys=[];
                $payment=[];


                //вывод если свои ордера в активных
                if ($item->creator_id == $this->user->id) {



                    $can_delete = 1; 

                    if ($item->ads->status == 5 || $item->ads->status == 7 || $item->ads->status == 2) {
                    $can_delete = 0;
                    }
                    if ($item->ads->amount > 1) {
                        $item->ads->amount = number_format($item->ads->amount, 2,'.','');
                    }
                    else {
                        $item->ads->amount = number_format($item->ads->amount, 10,'.','');
                        $item->ads->amount = (float)rtrim($item->ads->amount, '0');
                    }
                    if ($item->ads->amount == 0) {
                        $item->ads->amount = 0;
                    }
        
                    if ($item->ads->min_limit * $item->ads->course > 1) {
                        $item->ads->min_limit = number_format($item->ads->min_limit, 2, '.', '');
                    }
                    
        
                    if ($item->ads->max_limit * $item->ads->course > 1) {
                        $item->ads->max_limit = number_format($item->ads->max_limit, 2, '.', '');
        
                    }
                    
                    
                    if ($item->ads->course > 1) {
                        $item->ads->course = number_format($item->ads->course, 2, '.','');
                    }
                    $payments_creator = [];
                    $payments_author = [];
                    $payments_order = [];
                    //реквизиты author_id
                    $reqs_author = PaymentUser::find()->where(['user_id' => $item->author_id])->all();
                    if (!$reqs_author) {
                        $payments_author = null;
                    } else {
                    
                        foreach ($reqs_author as $payment) {
                            $payments_author[] = [
                                "id" => $payment->type->id,
                                "name" => $payment->type->name,
                                "value" => $payment->value,
                                "payment_receiver" => $payment->payment_receiver
                                
                            ];
                        }
                    }
                    
                    //реквизиты creator_id
                    $reqs_creator = PaymentUser::find()->where(['user_id' => $item->creator_id])->all();
                    if (!$reqs_creator) {
                        $payments_creator = null;
                    } else {
                    
                        foreach ($reqs_creator as $payment) {
                            $payments_creator[] = [
                                "id" => $payment->type->id,
                                "name" => $payment->type->name,
                                "value" => $payment->value,
                                "payment_receiver" => $payment->payment_receiver
                                
                            ];
                        }
                    }
                    //реквизиты order
                    $reqs_order = P2pPayment::find()->where(["p2p_ads_id" => $item->p2p_ads_id])->all();
                    foreach ($reqs_order as $payment) {
                        $payments_order[] = [
                            "id" => $payment->paymentType->id,
                            "name" => $payment->paymentType->name

                        ];
                    }

                    
                    $data[] = [
                    "order_id" => $item->ads->id,
                    "uuid" => $item->ads->uuid,
                    "date" => date("Y-m-d H:i:s", $item->ads->date),
                    "user_id" => $item->ads->user->id,
                    "user" => $item->ads->user->login,
                    "first_name" => $item->ads->user->first_name,
                    "last_name" => $item->ads->user->last_name,
                    "patronymic" => $item->ads->user->patronymic,
                    "verify_status" => $item->ads->user->verify_status,
                    "type" => $item->ads->type,
                    "chart_id" => $item->ads->chart->id,
                    "chart" => $item->ads->chart->symbol,
                    "currency" => $item->ads->currency->symbol,
                    "currency_id" => $item->ads->currency_id,
                    "full_amount" => (float)$item->ads->start_amount,
                    "amount" => (float)$item->ads->amount,
                    "course" => (float)$item->ads->course,
                    "min_limit" => (float)$item->ads->min_limit,
                    "max_limit" => (float)$item->ads->max_limit,
                    "duration" => $item->ads->duration / 60,
                    "payments_author" => $payments_author,
                    "payments_creator" => $payments_creator,
                    "payments_order" => $payments_order,
                    "status" => $item->ads->status,
                    "can_delete" => $can_delete,
                    "image" => Url::to([$item->ads->user->getImage()->getUrl("75x75")], "https"),
                    "order_id_history" => $item->id,
                    "volume" => (float)$item->price,
                    "start_date" => date("Y-m-d H:i:s", $item->start_date),
                    "end_date" => date("Y-m-d H:i:s", $item->end_date),
                    "author_id" => $item->author_id,
                    "author" => $item->author->login,
                    "image_author" => Url::to([$item->author->getImage()->getUrl("75x75")], "https"),
                    "creator_id" => $item->creator_id,
                    "status_history" => $item->status,
                    "description" => $item->ads->description
                    ];
                }

                //вывод если я в чужих ордерах
                if ($item->author_id == $this->user->id) {



                    $can_delete = 1; 

                    if ($item->ads->status == 5 || $item->ads->status == 7 || $item->ads->status == 2) {
                    $can_delete = 0;
                    }
                    if ($item->ads->amount > 1) {
                        $item->ads->amount = number_format($item->ads->amount, 2,'.','');
                    }
                    else {
                        $item->ads->amount = number_format($item->ads->amount, 10,'.','');
                        $item->ads->amount = (float)rtrim($item->ads->amount, '0');
                    }
                    if ($item->ads->amount == 0) {
                        $item->ads->amount = 0;
                    }
        
                    if ($item->ads->min_limit * $item->ads->course > 1) {
                        $item->ads->min_limit = number_format($item->ads->min_limit, 2, '.', '');
                    }
                    
        
                    if ($item->ads->max_limit * $item->ads->course > 1) {
                        $item->ads->max_limit = number_format($item->ads->max_limit, 2, '.', '');
        
                    }
                    
                    
                    if ($item->ads->course > 1) {
                        $item->ads->course = number_format($item->ads->course, 2, '.','');
                    }
                    $payments_creator = [];
                    $payments_author = [];
                    $payments_order = [];
                    //реквизиты author_id
                    $reqs_author = PaymentUser::find()->where(['user_id' => $item->author_id])->all();
                    if (!$reqs_author) {
                        $payments_author = null;
                    } else {
                    
                        foreach ($reqs_author as $payment) {
                            $payments_author[] = [
                                "id" => $payment->type->id,
                                "name" => $payment->type->name,
                                "value" => $payment->value,
                                "payment_receiver" => $payment->payment_receiver
                                
                            ];
                        }
                    }
                    
                    //реквизиты creator_id
                    $reqs_creator = PaymentUser::find()->where(['user_id' => $item->creator_id])->all();
                    if (!$reqs_creator) {
                        $payments_creator = null;
                    } else {
                    
                        foreach ($reqs_creator as $payment) {
                            $payments_creator[] = [
                                "id" => $payment->type->id,
                                "name" => $payment->type->name,
                                "value" => $payment->value,
                                "payment_receiver" => $payment->payment_receiver
                                
                            ];
                        }
                    }
                    //реквизиты order
                    $reqs_order = P2pPayment::find()->where(["p2p_ads_id" => $item->p2p_ads_id])->all();
                    foreach ($reqs_order as $payment) {
                        $payments_order[] = [
                            "id" => $payment->paymentType->id,
                            "name" => $payment->paymentType->name

                        ];
                    }

                    
                    $data[] = [
                    "order_id" => $item->ads->id,
                    "uuid" => $item->ads->uuid,
                    "date" => date("Y-m-d H:i:s", $item->ads->date),
                    "user_id" => $item->ads->user->id,
                    "user" => $item->ads->user->login,
                    "first_name" => $item->ads->user->first_name,
                    "last_name" => $item->ads->user->last_name,
                    "patronymic" => $item->ads->user->patronymic,
                    "verify_status" => $item->ads->user->verify_status,
                    "type" => $item->ads->type,
                    "chart_id" => $item->ads->chart->id,
                    "chart" => $item->ads->chart->symbol,
                    "currency" => $item->ads->currency->symbol,
                    "currency_id" => $item->ads->currency_id,
                    "full_amount" => (float)$item->ads->start_amount,
                    "amount" => (float)$item->ads->amount,
                    "course" => (float)$item->ads->course,
                    "min_limit" => (float)$item->ads->min_limit,
                    "max_limit" => (float)$item->ads->max_limit,
                    "duration" => $item->ads->duration / 60,
                    "payments_author" => $payments_author,
                    "payments_creator" => $payments_creator,
                    "payments_order" => $payments_order,
                    "status" => $item->ads->status,
                    "can_delete" => $can_delete,
                    "image" => Url::to([$item->ads->user->getImage()->getUrl("75x75")], "https"),
                    "order_id_history" => $item->id,
                    "volume" => (float)$item->price,
                    "start_date" => date("Y-m-d H:i:s", $item->start_date),
                    "end_date" => date("Y-m-d H:i:s", $item->end_date),
                    "author_id" => $item->author_id,
                    "author" => $item->author->login,
                    "image_author" => Url::to([$item->author->getImage()->getUrl("75x75")], "https"),
                    "creator_id" => $item->creator_id,
                    "status_history" => $item->status,
                    "description" => $item->ads->description
                    ];
                }


            }
            

            return $data;

    }


    /**
     * @SWG\Get(
     *    path = "/p2p/get-status-list",
     *    tags = {"p2p"},
     *    summary = "Список Статусов",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список статусов",
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

   
     public function actionGetStatusList()
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

        $result = StatusType::find()->all();

        

        return $result;
     }

     

     protected function sendCode($user, $id, $email = null)
    {
        Yii::warning('Отправка уведомления об операции');
        $deal = P2pHistory::find()->where(['id' => $id])->one();
        if ($deal) {
            if ($email || $user->email) {
                Yii::$app->mailer->compose()
                    ->setTo($user->email)
                    ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                    ->setSubject("Уведомление об операции")
                    ->setTextBody("Вам поступило предложение по сделке № ".$deal->p2p_ads_id . ", на сумму: ".$deal->price . $deal->ads->chart->symbol)
                    ->send();
            }
        }
    }



}  
