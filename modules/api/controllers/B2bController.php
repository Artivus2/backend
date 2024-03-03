<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\web\Controller;
use app\models\Chart;
use app\models\Currency;
use app\models\User;
use app\models\Upload;
use app\models\Wallet;
use app\models\B2bAds;
use app\models\B2bHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\StatusType;
use app\models\PaymentUser;
use app\models\B2bPayment;
use app\components\Cryptomus\Client;
use app\components\Cryptomus\Payment;
use app\models\Company;
use app\models\Okveds;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;


class B2bController extends BaseController
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
     *    path = "/b2b/create-sell",
     *    tags = {"b2b"},
     *    summary = "Создать обьявление о продаже криптовалюты на b2b (SELL)",
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
     *      name="discount",
     *      in="body",
     *      description="%",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="main_okved",
     *      in="body",
     *      description="ид основного ОКВЕД",
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
        $b2b = new B2bAds(["date" => time(), "company_id" => $this->user->id]);
        $b2b->currency_id = Yii::$app->request->post("currency_id",1);
        $b2b->description = Yii::$app->request->post("description", 'стандартные условия');

        


        
        $b2b->amount = (float)Yii::$app->request->post("amount"); //количество для продажи
        $b2b->start_amount = $b2b->amount;
        if(!$b2b->amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указано количество"];
        }


        $b2b->course = (float)Yii::$app->request->post("course"); //курс
        if(!$b2b->course) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан курс"];
        }

        $b2b->min_limit = (float)Yii::$app->request->post("min_limit");
        if(!$b2b->min_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан минимальный лимит для сделки"];
        }

        
        $b2b->max_limit = (float)Yii::$app->request->post("max_limit");
        $b2b->discount = (float)Yii::$app->request->post("discount", 0);
        if(!$b2b->max_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан максимальный лимит для сделки"];
        }

        $b2b->chart_id = Yii::$app->request->post("chart_id");
        if(!$b2b->chart_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан тип криптовалюты"];
        }

        $b2b->main_okved = Yii::$app->request->post("main_okved");
        if(!$b2b->main_okved) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан ОКВЭД"];
        }

        

        $b2b->duration = 900;
        
        
        $b2b->type = 2; //sell
        


        $chart = Chart::findOne(["id" => $b2b->chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        if ($chart->b2b == 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не входит в список доступных для b2b"];
        }
        
        $paymentsIDs = [1000];

        
        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" =>$chart->id, 'type' => 1]); //
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0, "type" => 1]);
            return ["success" => false, "message" => "Необходимо пополнить кошелек перед созданием ордера на продажу"];
        }

        if ($b2b->currency_id == 1) {
            if ($b2b->min_limit < 500) {
            return ["success" => false, "message" => "Минимальное количество должно быть больше 500 руб"];
            }
        }
        if ($b2b->max_limit / $b2b->course > $wallet->balance) {
        return ["success" => false, "message" => "Превышен лимит текущего баланса"];
        }
        if ($b2b->max_limit / $b2b->course > $b2b->amount) {
        return ["success" => false, "message" => "Превышен максимальный лимит суммы ордера"];
        }
        if ($b2b->max_limit < $b2b->min_limit) {
        return ["success" => false, "message" => "Минимальный лимит меньше максимального"];
        }

        if ($b2b->discount < 0 && $b2b->discount > 30) {
            return ["success" => false, "message" => "Вне допустимого предела"];
            }


        if($b2b->type == 2) {
            $wallet->balance -= $b2b->amount; //резервирование средств с финансового кошелька для продажи криптовалюты
        }

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $b2b->wallet_id = $wallet->id;
        $b2b->uuid = rand(100000000,999999999);
        $b2b->status = $status;


        


        $company_payment = Company::find()->where(['user_id' => $this->user->id])->all();
        foreach ($company_payment as $payment) {
            if (!$payment->inn || !$payment->name || !$payment->ogrn || !$payment->address || !$payment->kpp || !$payment->fio || !$payment->bank || !$payment->bik || !$payment->rs || !$payment->ks) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Не все обязательные реквизиты заполнены для создания ордера, заполните их в профиле компании"];
            }
        }

        if(!$b2b->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }

        $data = [];
        $data['id'] = $b2b->id;
        $data['uuid'] = $b2b->uuid;


        return ["success" => true, "message" => "Объявление о продаже криптовалюты успешно опубликовано", "data" => $data, "wallet" => $wallet];
     }


     /**
     * @SWG\Post(
     *    path = "/b2b/create-buy",
     *    tags = {"b2b"},
     *    summary = "Создать обьявление о покупке криптовалюты на b2b (BUY)",
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
     *      name="discount",
     *      in="body",
     *      description="%",
     *      @SWG\Schema(type="number")
     *     ),
     *    @SWG\Parameter(
     *      name="main_okved",
     *      in="body",
     *      description="ид основного ОКВЕД",
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
     *      description = "Создание ордера b2b на покупку (статус Создан (PENDING)",
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

        $b2b = new B2bAds(["date" => time(), "company_id" => $this->user->id]);
        $b2b->currency_id = Yii::$app->request->post("currency_id", 1);
        
        $b2b->description = Yii::$app->request->post("description", 'стандартные условия');
        $b2b->amount = (float)Yii::$app->request->post("amount"); //количество для покупки
        $b2b->start_amount = $b2b->amount;
        if(!$b2b->amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указано количество"];
        }


        $b2b->course = (float)Yii::$app->request->post("course"); //курс
        $b2b->discount = (float)Yii::$app->request->post("discount", 0); //курс
        if(!$b2b->course) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан курс"];
        }

        $b2b->min_limit = (float)Yii::$app->request->post("min_limit");
        if(!$b2b->min_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан минимальный лимит для сделки"];
        }

        
        $b2b->max_limit = (float)Yii::$app->request->post("max_limit");
        if(!$b2b->max_limit) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан максимальный лимит для сделки"];
        }

        $b2b->chart_id = Yii::$app->request->post("chart_id");
        if(!$b2b->chart_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан тип криптовалюты"];
        }

        $b2b->main_okved = Yii::$app->request->post("main_okved", null);
        if(!$b2b->main_okved) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан ОКВЭД"];
        }

        $b2b->duration = 900;

        
        $b2b->type = 1; //buy
        $status = -1;  //создать ордер


        $chart = Chart::findOne(["id" => $b2b->chart_id]);
        if (!$chart) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не найдена"];
        }

        if ($chart->b2b == 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Валюта не входит в список доступных для b2b"];
        }
        
        
        $wallet = Wallet::findOne(["user_id" => $this->user->id, "chart_id" =>$chart->id]); //
        if(!$wallet) {
            $wallet = new Wallet(["user_id" => $this->user->id, "chart_id" => $chart->id, "balance" => 0, "type" => 0]);
            //return ["success" => false, "message" => "Необходимо пополнить кошелек перед созданием ордера на продажу"];
        }

        if ($b2b->currency_id == 1) {
            if ($b2b->min_limit < 500) {
            return ["success" => false, "message" => "Минимальное количество должно быть больше 500 руб"];
            }
        }

        if ($b2b->max_limit < $b2b->min_limit) {
        return ["success" => false, "message" => "Минимальный лимит меньше максимального"];
        }

        if ($b2b->min_limit / $b2b->course > $b2b->amount) {
        return ["success" => false, "message" => "Минимальный лимит меньше суммы предложения"];
        }

        if ($b2b->discount < 0 && $b2b->discount > 30) {
            return ["success" => false, "message" => "Вне допустимого предела"];
            }
        

        if(!$wallet->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения счета"];
        }

        $b2b->wallet_id = $wallet->id;
        $b2b->uuid = rand(100000000,999999999);
        $b2b->status = $status;
        


        

        $company_payment = Company::find()->where(['user_id' => $this->user->id])->all();
        foreach ($company_payment as $payment) {
            if (!$payment->inn || !$payment->name || !$payment->ogrn || !$payment->address || !$payment->kpp || !$payment->fio || !$payment->bank || !$payment->bik || !$payment->rs || !$payment->ks) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Не все обязательные реквизиты заполнены для создания ордера, заполните их в профиле компании"];
            }
        }

        if(!$b2b->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }

        $data = [];
        $data['id'] = $b2b->id;
        $data['uuid'] = $b2b->uuid;


        return ["success" => true, "message" => "Объявление о покупке криптовалюты успешно опубликовано", "data" => $data];
    }
   



    /**
     * @SWG\Post(
     *    path = "/b2b/edit-order",
     *    tags = {"b2b"},
     *    summary = "Редактирование обьявления автором",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      required=true,
     *      description="b2b_ads_id ордера",
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
     *      name="discount",
     *      in="body",
     *      description="%",
     *      @SWG\Schema(type="number")
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
        

        $b2b_ads_id = Yii::$app->request->post("b2b_ads_id");

        $b2bAds_history = B2bHistory::find()->where(["b2b_ads_id" => $b2b_ads_id])->all();

        if($b2bAds_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "По ордеру есть история, не может быть отредакируем"];
        }

        if(!$b2b_ads_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан b2b_ads_id ордера"];
        }
        $result = B2bAds::find()->where(["id" => $b2b_ads_id, "status" => -1])->one();
        if (!$result) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не найден"];
        }

        if ($result->amount != $result->start_amount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть отредактирован так как по нему имеются заверешенные сделки, создайте новый ордер и удалите текущий"];
        }

        if($result->status != -1) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть отредактирован, либо он отменен системой"];
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
        $discount = (float)Yii::$app->request->post("discount", 0);
        if(!$amount) {
            $amount = $result->amount;

        }

        $current_wallet = Wallet::find()->where(["user_id" => $result->company_id, "chart_id" => $chart_id])->one();
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
       
       

        if ($result){
            $result->currency_id = $currency_id;
            $result->amount = $amount;
            $result->start_amount = $amount;
            $result->min_limit = $min_limit;
            $result->max_limit = $max_limit;
            $result->course = $course;
            $result->discount = $discount;
            $result->description = Yii::$app->request->post("description", $result->description);
            $result->status = -1; //PENDING again
            $result->date = time();
            $result->save();
            return ["success" => true, "message" => "Ордер успешно изменен"];

        }
     

        
    }

    
    /**
     * @SWG\Delete(
     *    path = "/b2b/remove-order",
     *    tags = {"b2b"},
     *    summary = "Удаление обьявления после отмены",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="b2b_ads_id ордера",
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
     *      description = "Удаление ордера b2b(статус удален (REMOVED)",
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

        $b2b_ads_id = Yii::$app->request->post("b2b_ads_id");
        if (!$b2b_ads_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не указан ID ордера"];
        }
        $b2b_ads_history = B2bHistory::find()->where(['b2b_ads_id' => $b2b_ads_id, "creator_id" => $this->user->id, 'status' => 2])->all();
        if ($b2b_ads_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть удален, имеются оплаченые но не завершенные сделки по нему"];            
        }

        $result = B2bAds::find()->where(["id" => $b2b_ads_id, "company_id" => $this->user->id, "status" => 6])->one();
        if(!$result) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не может быть удален, отмените остатки перед удалением"];
        }
        
       
        $result->status = 9;
        if ($result->save()) {
            return ["success" => true, "message" => "Ордер успешно удален"];
        }
                   
            
         
    }


         
    /**
     * @SWG\Get(
     *    path = "/b2b/full-list",
     *    tags = {"b2b"},
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
     *      name="company_id",
     *      in="path",
     *      description="ID компании (без указания, только свои сделки)",
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
     *      name="amount",
     *      in="path",
     *      description="фильтр по сумме (в рублях)",
     *      type="integer",
     *     ),
     *    @SWG\Parameter(
     *      name="discount",
     *      in="path",
     *      description="%",
     *      type="number",
     *     ),
     *    @SWG\Parameter(
     *      name="bank",
     *      in="path",
     *      description="фильтр по банку",
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
     *    @SWG\Parameter(
     *      name="main_okved",
     *      in="path",
     *      description="ОКВЕД",
     *      type="string",
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     @SWG\Schema(ref = "#/definitions/B2bAds")
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
        $b2b_ads_id = Yii::$app->request->get("uuid");
        if(!$b2b_ads_id) {
            $whereid = ["IS NOT","b2b_ads.uuid", null];
        } else {
            $whereid = ["b2b_ads.uuid" => $b2b_ads_id]; 
        }
        $all_orders = 0;
        $author_id = Yii::$app->request->get("author_id");

        if(!$author_id) {
             $all_orders = 1;
        }
        
        $companyIDs = array();
        $companys = Yii::$app->request->get("company_id");
        $companyIDs = explode(",", $companys);
        if(!$companys) {
            $whereusers = ["<>", "b2b_ads.company_id", $this->user->id];
        } else {
            $whereusers = ["in", "b2b_ads.company_id", $companyIDs];
        }

        $statusIDs = array();
        $statuses = Yii::$app->request->get("status");
        $statusIDs = explode(",", $statuses);
        if(!$statuses) {
            $wherestatus = ["IS NOT", "b2b_ads.status", null];
        } else {
            $wherestatus = ["in", "b2b_ads.status", $statusIDs];
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
            $wheretype = ["IS NOT","b2b_ads.type", null];
        } else {
            $wheretype = ["b2b_ads.type" => $type]; 
            
        }

        $okved = Yii::$app->request->get("main_okved");
        
        
        if(!$okved) {
            $whereokved = ["chart.active" => 1];
        } else {
            $whereokved = ["b2b_ads.main_okved" => $okved]; 
            
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

        $summ = (float)Yii::$app->request->get("amount");
        
        if(!$summ) {
            $wheresummmin = ["IS NOT","min_limit", null];
            $wheresummmax = ["IS NOT","max_limit", null];
        } else {
            $wheresummmin =  ['<=','min_limit', $summ]; 
            $wheresummmax =  ['>=','max_limit', $summ]; 
            
        }

        $discount = (float)Yii::$app->request->get("discount");
        if (!$discount) {
            $wherediscount = ["currency.active" => 1];
        } else {
            $wherediscount = ['<=','discount', $discount]; 
        }

        $bank = Yii::$app->request->get("bank_id");

        if (!$bank) {
            $wherebank = ["currency.active" => 1];
            
        }
        else {
            $wherebank = ["company.bank" => $bank];
        }
        
                
        $data = [];
        $b2bAds_query = B2bAds::find()->joinwith(['chart','currency','company'])
        ->where($whereid)
        ->andwhere($wheretype)
        ->andwhere($wherechart)
        ->andwhere($wherecurrency)
        ->andwhere($wherestatus)
        ->andWhere($whereusers)
        ->andWhere($whereokved)
        ->andWhere($wheresummmin)
        ->andWhere($wheresummmax)
        ->andWhere($wherediscount)
        ->andWhere($wherebank)
        ->all();

        foreach ($b2bAds_query as $item)
        {
            $b2bAds_query_count = B2bAds::find()->where(["company_id" => $item->user->id])->count();
            $b2bAds_query_count_complete = B2bAds::find()->where(["company_id" => $item->user->id, "status" => 10])->count();
            $complete = number_format($b2bAds_query_count_complete / $b2bAds_query_count, 2, '.','');
            $b2bAds_history = B2bHistory::find()->where(["creator_id" => $item->user->id, "b2b_ads_id" => $item->id])->joinwith(['company'])->andwhere($wherestatush)->all();
            
            if (!$b2bAds_history) {
                
                $historys = null;
                if ($statusesh) {
                    continue;
                }
                
            } else {
            $historys = [];
            if (!$author_id) {
                
                foreach ($b2bAds_history as $history) {
                    if ($history->price > 1) {
                        $history->price = number_format($history->price, 2,'.','');
                    }
                    else {
                        $history->price = number_format($history->price, 10,'.','');
                        $history->price = rtrim($history->price, '0');
                    }
                    
                    //$history_payment = PaymentUser::find()->joinwith(['type'])->where(['user_id' => $history->creator_id, "payment_id" => $history->payment_id])->one();                    
                    //$history_company_payment = 
                    $historys[]=[
                        "order_id" => $history->b2b_ads_id,
                        "volume" => $history->price,
                        "start_date" => date("Y-m-d H:i:s", $history->start_date),
                        "end_date" => date("Y-m-d H:i:s", $history->end_date),
                        "bank" => $history->company->bank??'не указан',
                        "bik" => $history->company->bik??'не указан',
                        "rs" => $history->company->rs??'не указан',
                        "ks" => $history->company->ks??'не указан',
                        "phone" => $history->company->phone??'не указан',
                        "author_id" => $history->author_id,
                        "creator_id" => $history->creator_id,
                        "status_history" => $history->status,
                    ];

                    }
                } else {
                    $b2bAds_history = B2bHistory::find()->where(["author_id" => $author_id, "b2b_ads_id" => $item->id])->joinwith(['company'])->andwhere($wherestatush)->all();
                    if (!$b2bAds_history) {
                    continue;
                    } else {
                        foreach ($b2bAds_history as $history) {
                            if ($history->price > 1) {
                                $history->price = number_format($history->price, 2,'.','');
                            }
                            else {
                                $history->price = number_format($history->price, 10,'.','');
                                $history->price = rtrim($history->price, '0');
                            }
                            
                            //$history_payment = PaymentUser::find()->joinwith(['type'])->where(['user_id' => $history->creator_id, "payment_id" => $history->payment_id])->one();

                            $historys[]=[
                                "order_id" => $history->b2b_ads_id,
                                "volume" => $history->price,
                                "start_date" => date("Y-m-d H:i:s", $history->start_date),
                                "end_date" => date("Y-m-d H:i:s", $history->end_date),
                                "bank" => $history->company->bank??'не указан',
                                "bik" => $history->company->bik??'не указан',
                                "rs" => $history->company->rs??'не указан',
                                "ks" => $history->company->ks??'не указан',
                                "phone" => $history->company->phone??'не указан',
                                "author_id" => $history->author_id,
                                "creator_id" => $history->creator_id,
                                "status_history" => $history->status,
                            ];
                        }
                    }
                }
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
                "bank" => $bank,
                "company_id" => $item->user->id,
                "company" => $item->company->name,
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
                "history" => $historys,
                "status" => $item -> status,
                "can_delete" => $can_delete,
                "image" => Url::to([$item->user->getImage()->getUrl("75x75")], "https"),
                "user_orders_count" => (int)$b2bAds_query_count,
                "user_orders_oount_complete_percent" =>(int)$complete,
                "okved" => $item->main_okved,
                'description' => $item->description,
                'discount' => $item->discount
                
            ];
        }
        
        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/b2b/confirm-trade",
     *    tags = {"b2b"},
     *    summary = "Подтверждение участия в сделке",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads",
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
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Подтверждено участие в ордере",
     *      @SWG\Schema(ref = "#/definitions/B2bHistory")
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


        $b2b_ads_id = Yii::$app->request->post("b2b_ads_id");
        
        
        $b2b_ads = B2bAds::find()->where(["id" => $b2b_ads_id])->andWhere(['status' => -1])->one();

        

        if(!$b2b_ads) {
            Yii::$app->response->statusCode = 401;

            return ["success" => false, "message" => "Ордер не найден"];
        }

        if ($b2b_ads->currency_id == 1) {
            if((float)Yii::$app->request->post("offer") < 500) {
                    Yii::$app->response->statusCode = 401;
                    return ["success" => false, "message" => "Сумма должна быть больше 500 р."];
                }
        }

        
        $b2bAds_history = B2bHistory::find()->where(["author_id" => $this->user->id, "status" => [1,2]])->one();
        if($b2bAds_history) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "У вас есть активный ордер с текущим ользователем"];
        }

        $chart_name = Chart::find()->where(['id' => $b2b_ads->chart_id])->one();
        $currency_name = Currency::find()->where(['id' => $b2b_ads->currency_id])->one();


        //для входа в сделку на покупку крипта у author_id

        if ($b2b_ads->type == 1) {
            $seller_offer = (float)Yii::$app->request->post("offer"); 
            $wallet_seller = Wallet::find()->where(['user_id' => $this->user->id, 'chart_id' => $b2b_ads->chart_id])->one(); //наличие chart на кошельке чувака
            if (!$wallet_seller) {
        
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Отсутствует кошелек"];    
                }
            if ($wallet_seller->balance < 0) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Не достаточно средств для осуществления операции"];    
                }
            if ($wallet_seller->balance < $seller_offer / $b2b_ads->course) { //крипта покупателя

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Недостаточно средств на балансе"];    
                }
            $wallet_seller->balance -= $seller_offer / $b2b_ads->course;
            $status = 1; // торгуется (в течении срока оплаты)
            $b2b_h = new B2bHistory(["start_date" => time(), "author_id" => $this->user->id, "creator_id" => $b2b_ads->company_id, "status" => $status, 'payment_id' => 1000,'file_path' => "не оплачен"]);

            $trade_time = $b2b_ads->duration; //срок оплаты

            
            $duration = "+15 minutes";
            

            $b2b_h->end_date = strtotime($duration, $b2b_h->start_date); //
            $b2b_h->b2b_ads_id = Yii::$app->request->post("b2b_ads_id");
            $b2b_h->price = $seller_offer / $b2b_ads->course; //в крипте
            
            if($b2b_ads->min_limit > $b2b_h->price * $b2b_ads->course) {
                Yii::$app->response->statusCode = 400;
                $min_author_price = $b2b_ads->min_limit / $b2b_ads->course;
                if ($min_author_price > 1) {
                    $min_author_price = number_format($min_author_price, 2,'.','');
                }
                else {
                    $min_author_price = number_format($min_author_price, 10,'.','');
                    $min_author_price = rtrim($min_author_price, '0');
                }
                return ["success" => false, "message" => "Минимальная сумма " . $min_author_price . " " .$chart_name->symbol . ", указано: " . $b2b_h->price . " " .$chart_name->symbol];
            }
    
            $max_limit = $b2b_ads->amount * $b2b_ads->course;
            
            if($b2b_h->price > $b2b_ads->max_limit / $b2b_ads->course) {
                Yii::$app->response->statusCode = 400;
                $max_author_price = $b2b_ads->max_limit / $b2b_ads->course;
                if ($max_author_price > 1) {
                    $max_author_price = number_format($max_author_price, 2,'.','');
                }
                else {
                    $max_author_price = number_format($max_author_price, 10,'.','');
                    $max_author_price = rtrim($max_author_price, '0');
                }
                
                return ["success" => false, "message" => "Максимальная сумма " . $max_author_price . " " . $chart_name->symbol . ", указано: " . $b2b_h->price . " " .$chart_name->symbol];
            }

            $ostatok = $b2b_ads->amount;
            $b2b_ads->amount -= $b2b_h->price;


            if ($b2b_ads->amount < 0) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сумма ордера больше максимального остатка", $ostatok. " " . $chart_name->symbol . " (" . $ostatok * $b2b_ads->course . " " . $currency_name->name . ")"];
            }

            if(!$b2b_ads->save()) {
                
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения объявления"];
            }

            if(!$b2b_h->save()) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения сделки (в истории)"];
            }
            if(!$wallet_seller->save()) {

                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


            $data = [
                "id" => $b2b_h->b2b_ads_id,
                "offer" => (float)$b2b_h->price
            ];

            $company = User::find()->where(['id' => $b2b_ads->company_id])->one();
            if ($company) {
                $this->sendCode($company, $b2b_h->id);
            }
            return ["success" => true, "message" => "Подтверждено участие в сделке (покупка)", "data" => $data];

            
        }

        //sell
      
        $chart_name = Chart::find()->where(['id' => $b2b_ads->chart_id])->one();
        $status = 1; // торгуется (в течении срока оплаты)
        $b2b_h = new B2bHistory(["start_date" => time(), "author_id" => $this->user->id, "creator_id" => $b2b_ads->company_id, "status" => $status, "payment_id" => 1000, 'file_path' => "не оплачен"]);

        $trade_time = $b2b_ads->duration; //срок оплаты

        
        $duration = "+15 minutes";
        




        $b2b_h->end_date = strtotime($duration, $b2b_h->start_date); //
        $b2b_h->b2b_ads_id = Yii::$app->request->post("b2b_ads_id");
        //$b2b_h->payment_id = Yii::$app->request->post("payment");
        $b2b_h->price = (float)Yii::$app->request->post("offer") / $b2b_ads->course; //оффер в рублях в базу в крипте
        //$author_price = $b2b_h->price / $b2b_ads->course;  //сумма покупателя * курс продавца бтс


        if($b2b_ads->min_limit > $b2b_h->price * $b2b_ads->course) {
            Yii::$app->response->statusCode = 400;
            $min_author_price = $b2b_ads->min_limit / $b2b_ads->course;
            if ($min_author_price > 1) {
                $min_author_price = number_format($min_author_price, 2,'.','');
            }
            else {
                $min_author_price = number_format($min_author_price, 10,'.','');
                $min_author_price = rtrim($min_author_price, '0');
            }
            return ["success" => false, "message" => "Минимальная сумма " . $min_author_price . " " .$chart_name->symbol. ", указано: " . $b2b_h->price. " " .$chart_name->symbol];
        }

        $max_limit = $b2b_ads->amount * $b2b_ads->course;
        
        if($b2b_h->price * $b2b_ads->course > $max_limit) {
            Yii::$app->response->statusCode = 400;
            $max_author_price = $b2b_ads->max_limit / $b2b_ads->course;
            if ($max_author_price > 1) {
                $max_author_price = number_format($max_author_price, 2,'.','');
            }
            else {
                $max_author_price = number_format($max_author_price, 10,'.','');
                $max_author_price = rtrim($max_author_price, '0');
            }
            
            return ["success" => false, "message" => "Максимальная сумма " . $max_author_price . " " . $chart_name->symbol];
        }

        $b2b_ads->amount -= $b2b_h->price;

        if ($b2b_ads->amount < 0) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка! Сделка обновилась", $b2b_ads->amount];
        }

        //$b2b_ads->status = $status;

        if(!$b2b_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения объявления"];
        }

        if(!$b2b_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }


        $data = [
            "id" => $b2b_h->b2b_ads_id,
            "offer" => (float)$b2b_h->price

        ];

        $company = User::find()->where(['id' => $b2b_ads->company_id])->one();
        if ($company) {
            $this->sendCode($company, $b2b_h->id);
        }

        return ["success" => true, "message" => "Подтверждено участие в сделке", "data" => $data];
        
    }


     /**
     * Uploads payment document
     *
     * Подтверждение оплаты и загрузка документа об оплате. форматы файлов JPG, PNG и PDF.
     *
     * @SWG\Post(
     *    path = "/b2b/check-pay",
     *    tags = {"b2b"},
     *    summary = "Подтверждение оплаты и загрузка платежки",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="file",
     *      in="body",
     *      required=true,
     *      @SWG\Schema(
     *          @SWG\Property(
     *              property="file",
     *              type="file",
     *              description="Платежный документ"
     *          )
     *      )
     *    ),
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads в history",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "OK",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="string",
     *              property="file",
     *              description="URL загруженной фотографии"
     *          )
     *      )
     *    ),
     *    @SWG\Response(
     *      response = 400,
     *      description = "Ошибка запроса",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(
     *              type="boolean",
     *              property="success",
     *              description="Результат загрузки",
     *              example=false
     *          ),
     *          @SWG\Property(
     *              type="string",
     *              property="message",
     *              description="Строка с содержанием ошибки",
     *              example="Вы можете прикрепить jpg, png, pdf"
     *          ),
     *      )
     *    ),
     *    @SWG\Response(
     *      response = 401,
     *      description = "Ошибка авторизации",
     *      @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *              type="boolean",
     *              property="success",
     *              description="Success state - false",
     *              example=false
     *         ),
     *         @SWG\Property(
     *              type="string",
     *              property="message",
     *              description="Строка с содержанием ошибки",
     *              example="Token не найден"
     *         ),
     *      )
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

        $history_id = Yii::$app->request->post("b2b_ads_id");
        
        
        $b2b_ads = B2bAds::find()->where(['id' => $history_id, "status" => $maycancel])->one();
                        

        if (!$b2b_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена (основная)"];
        }
        
        if ($b2b_ads->type == 2) {
            $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'author_id' => $this->user->id, 'status' => 1])->one();
                }
        if ($b2b_ads->type == 1) {
                $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 1])->one();
                }
        
        
        
        if (!$b2b_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка в истории не найдена"];
        }

      
        if ((int)$b2b_h->end_date < time()) {
            if ($b2b_ads->type==2) {
                //$b2b_ads->status = 7;
                $b2b_h->status = 7;
                $b2b_ads->amount += $b2b_h->price;
                $b2b_h->price = 0;
                $b2b_ads->save();
                $b2b_h->save();
                //вернуть средства продавцу
                return ["success" => false, "message" => "Сделка просрочена, отменена системой"];
            }
            if ($b2b_ads->type==1) {
                $b2b_ads->status = 5;
                $b2b_h->status = 5;
                //$b2b_ads->amount += $b2b_h->price / $b2b_ads->course;
                $b2b_h->price = 0;
                $b2b_ads->save();
                $b2b_h->save();
                //аппеляция
                return ["success" => false, "message" => "Сделка просрочена,если вы оплатили средства обратитесь в техподдержку"];
            }

        }

        
        //$b2b_ads->status = 2; 
        $b2b_h->status = 2; //нажал кнопку платеж выполнен
        $b2b_h->end_date = strtotime('+2 days 23 hours 45 minutes', $b2b_h->end_date);
        $b2b_h->file_path = 'Оплачен';
        if (!$b2b_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Не удалось сохранить сделку"];
        }

        if (!$b2b_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ну удалось сохранить сделку в истории"];
        }


        return ["success" => true, "message" => "Сделка оплачена, ожидаем подтверждение"];
    
    }



    /**
     * @SWG\Post(
     *    path = "/b2b/cancel-order",
     *    tags = {"b2b"},
     *    summary = "Отмена ордера (или остатков)",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "отмена сделки",
     *      @SWG\Schema(ref = "#/definitions/B2bHistory")
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
        

        
        $history_id = Yii::$app->request->post("b2b_ads_id");
        //$desc_id = Yii::$app->request->post("description_id", 6);
        $b2b_ads = B2bAds::find()->where(['id' => $history_id, 'company_id'=> $this->user->id])->one();

        if ($b2b_ads->status == 5) {
            return ["success" => false, "message" => "Ордер не может быть отменен, статус В аппеляции, обратитесь к администратору"];
        }
        if (!$b2b_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ордер не найден"];
        }
        if ($b2b_ads->status == -1) 
        {

                
                if ($b2b_ads->type == 2) {
                    $wallet_seller = Wallet::find()->where(["user_id" => $b2b_ads->company_id, "chart_id" => $b2b_ads->chart_id])->one();
                    if (!$wallet_seller) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Невозможно пополнить баланс продавца"];
                    }
                    $wallet_seller->balance += $b2b_ads->amount; //вернуть средства (или остатки) продавцу на кошелек
                    $b2b_ads->amount = 0;
                    if(!$wallet_seller->save()) {
                        Yii::$app->response->statusCode = 400;
                        return ["success" => false, "message" => "Ошибка сохранения средств на кошельке"];
                    }

                }

                if(!$b2b_ads->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения ордера"];
                }

                return ["success" => true, "message" => "Ордер отменен"];
        }


     
     }

    /**
     * @SWG\Post(
     *    path = "/b2b/cancel-trade",
     *    tags = {"b2b"},
     *    summary = "Отмена сделки",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads",
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
     *      @SWG\Schema(ref = "#/definitions/B2bHistory")
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

        
        $history_id = Yii::$app->request->post("b2b_ads_id");
        $desc_id = Yii::$app->request->post("description_id", 6);
        $b2b_ads = B2bAds::find()->where(['id' => $history_id])->one();
        if ($b2b_ads->type == 1) {
        $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'creator_id' => $this->user->id, "status"=>$maycancel])->one();
        } else {
        $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'author_id' => $this->user->id, "status"=>$maycancel])->one();
        }

        if (!$b2b_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Нельзя отменить ордер, вторая сторона оплачивает фиат, либо оповестите в чате об отмене"];
        }
        
        if ($b2b_h->status == 2) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Нельзя отменить, ордер уже оплачен"];
        }
        

        if ($b2b_ads->status == -1) 
        {
                //$b2b_ads->status = 6;
                $b2b_h->status = 6;
                $b2b_h->description_id = $desc_id;
                if ($b2b_ads->type == 1) {
                    $b2b_ads->amount += $b2b_h->price; //вернуть средства в ордер
                    $wallet_seller = Wallet::find()->where(['user_id' => $b2b_h->author_id, 'chart_id' => $b2b_ads->chart_id])->one();

                    if (!$wallet_seller) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Не удалось вернуть средства на кошелек"];
                    }
                    $wallet_seller->balance += $b2b_h->price;
                    if (!$wallet_seller->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Не удалось сохранить параметры кошелька"];
                    }
                }
                if ($b2b_ads->type == 2) {
                    $b2b_ads->amount += $b2b_h->price; //вернуть средства в ордер
                }
                if(!$b2b_h->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения сделки"];
                }
                if(!$b2b_ads->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения сделки"];
                }

                return ["success" => true, "message" => "Сделка отменена"];
        }

     
     }


     /**
     * @SWG\Post(
     *    path = "/b2b/appeal",
     *    tags = {"b2b"},
     *    summary = "Аппеляция",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads",
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

        $history_id = Yii::$app->request->post("b2b_ads_id");

        $b2b_ads = B2bAds::find()->where(['id' => $history_id])->one();
        if (!$b2b_ads) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена"];
        }

        $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'author_id' => $this->user->id])->one();
        if (!$b2b_h) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Сделка не найдена (в истории)"];
        }

        

        if ($b2b_h->status == 2) {
            $b2b_ads->status = 5;
            $b2b_h->status = 5;
            
        } else {
            return ["success" => false, "message" => "Сделка еще не оплачена"];
        }
        

        //разбор
    
        if(!$b2b_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }
        if(!$b2b_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }

        return ["success" => true, "message" => "Сделка в аппеляции, рассматривается администратором"];

    }

    /**
     * @SWG\Post(
     *    path = "/b2b/confirm-payment",
     *    tags = {"b2b"},
     *    summary = "Подтверждение оплаты продавцом / покупателем",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="b2b_ads_id",
     *      in="body",
     *      description="id ордера b2b_ads",
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

        $history_id = Yii::$app->request->post("b2b_ads_id");
        $b2b_ads = B2bAds::find()->where(['id' => $history_id, 'status' => [-1,6]])->one();
        $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 2])->one();

        if (!$b2b_ads) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Ордер не найден"];
        }

        if ($b2b_ads->type == 2) {
            $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'creator_id' => $this->user->id, 'status' => 2])->one();
            if (!$b2b_h) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сделка не найдена (в истории)"];
            }
            //подтвердил оплату 
            if($b2b_ads->amount == 0) {
                $b2b_ads->status = 10;
            } else {
                $b2b_ads->status = -1;
                if ($b2b_ads->min_limit / $b2b_ads->course > $b2b_ads->amount) {
                    $b2b_ads->min_limit = $b2b_ads->amount * $b2b_ads->course;
                }
            }
            $b2b_h->status = 4;
            $wallet_seller = Wallet::findOne(["user_id" => $b2b_h->author_id, 'chart_id' => $b2b_ads->chart_id]);
            if (!$wallet_seller) {
                $wallet_seller = new Wallet(["user_id" => $b2b_h->author_id, "chart_id" => $b2b_ads->chart_id, "type" => 0]);
            }
            $wallet_seller->balance += $b2b_h->price;
            if(!$wallet_seller->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


        }



        if ($b2b_ads->type == 1) {
            $b2b_h = B2bHistory::find()->where(['b2b_ads_id' => $history_id, 'author_id' => $this->user->id, 'status' => 2])->one();
            if (!$b2b_h) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Сделка не найдена (в истории)"];
            }
            //подтвердил оплату 
            if($b2b_ads->amount == 0) {
                $b2b_ads->status = 10;
            } else {
                $b2b_ads->status = -1;
                if ($b2b_ads->min_limit / $b2b_ads->course > $b2b_ads->amount) {
                    $b2b_ads->min_limit = $b2b_ads->amount * $b2b_ads->course;
                }
            }
            $b2b_h->status = 4;
            $wallet_buyer = Wallet::findOne(["user_id" => $b2b_h->creator_id, 'chart_id' => $b2b_ads->chart_id]);
            if (!$wallet_buyer) {
                $wallet_buyer = new Wallet(["user_id" => $b2b_h->creator_id, "chart_id" => $b2b_ads->chart_id]);
            }
            $wallet_buyer->balance += $b2b_h->price;
            if(!$wallet_buyer->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения кошелька"];
            }


        }
        if(!$b2b_ads->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки"];
        }
        $b2b_h->file_path = 'Исполнен';
        if(!$b2b_h->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Ошибка сохранения сделки (в истории)"];
        }
        

        return ["success" => true, "message" => "Сделка подтверждена"];
     
     
    }

 /**
     * @SWG\Get(
     *    path = "/b2b/history",
     *    tags = {"b2b"},
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
     *     @SWG\Schema(ref = "#/definitions/B2bHistory")
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
        $b2b_ads_id = Yii::$app->request->get("id");
        if(!$b2b_ads_id) {
            $whereid = ["IS NOT","b2b_ads.id", null];
        } else {
            $whereid = ["b2b_ads.id" => $b2b_ads_id]; 
        }
        $all_orders = 0;
        $author_id = Yii::$app->request->get("author_id");
        
        if(!$author_id) {
             $all_orders = 1;
        }

        

        $userIDs = array();
        $users = Yii::$app->request->get("user_id");
        $userIDs = explode(",", $users);
        if(!$users) {
            $whereusers = ["IS NOT", "b2b_ads.company_id", null];
        } else {
            $whereusers = ["in", "b2b_ads.company_id", $userIDs];
        }

        $statusIDs = array();
        $statuses = Yii::$app->request->get("status");
        $statusIDs = explode(",", $statuses);
        if(!$statuses) {
            $wherestatus = ["IS NOT", "b2b_ads.status", null];
        } else {
            $wherestatus = ["in", "b2b_ads.status", $statusIDs];
        }

        $statushIDs = array();
        $statusesh = Yii::$app->request->get("status_history");
        $statushIDs = explode(",", $statusesh);
        if(!$statusesh) {
            $wherestatush = ["IS NOT", "b2b_history.status", null];
        } else {
            $wherestatush = ["in", "b2b_history.status", $statushIDs];
        }


        $type = Yii::$app->request->get("type");
        
        if(!$type) {
            $wheretype = ["IS NOT","b2b_ads.type", null];
        } else {
            $wheretype = ["b2b_ads.type" => $type]; 
            
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
            $whereduration = ["IS NOT","b2b_ads.duration", null];
        } else {
            $whereduration = ["b2b_ads.duration" => $duration * 60];

        }


                
        $data = [];
        $b2bAds_query = B2bHistory::find()->joinwith(['ads'])
        ->where($whereid)

        ->andwhere($wherestatush)

        ->all();

        foreach ($b2bAds_query as $item)
            {   
                $historys=[];
                $payment=[];
                //$b2b_reqs_creator = PaymentUser::find()->where(['user_id' => $item->creator_id, "payment_id" => $item->payment_id])->joinWith(['type'])->one();
                
                //$b2b_reqs_author = PaymentUser::find()->where(['user_id' => $item->author_id, "payment_id" => $item->payment_id])->joinWith(['type'])->one();
                $author_info = Company::find()->where(["user_id" => $item->author_id])->one();

                if ($item->ads->company_id == $this->user->id) {
                    
                    $can_delete = 1; 

                    if ($item->ads->status == 6 || $item->ads->status == 6) {
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


                    $data[] = [
                    "b2b_ads_id" => $item->ads->id,
                    "uuid" => $item->ads->uuid,
                    "date" => date("Y-m-d H:i:s", $item->ads->date),
                    "company_id" => $item->ads->user->id,
                    "company" => $item->ads->company->name,
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
                    "author_id" => $item->author_id,
                    "author" => $author_info->name ?? null,
                    "author_bank" => $author_info->bank ?? null,
                    "author_bik" => $author_info->bik ?? null,
                    "author_rs" => $author_info->rs ?? null,
                    "author_ks" => $author_info->ks ?? null,
                    "author_phone" => $author_info->phone ?? null,
                    "image_author" => Url::to([$item->user->getImage()->getUrl("75x75")], "https"),
                    "creator" => $item->company->name,
                    "creator_bank" => $item->company->bank,
                    "creator_id" => $item->creator_id,
                    "creator_bik" => $item->company->bik,
                    "creator_rs" => $item->company->rs,
                    "creator_ks" => $item->company->ks,
                    "creator_phone" => $item->company->phone,
                    "image_creator" => Url::to([$item->ads->user->getImage()->getUrl("75x75")], "https"),
                    "status" => $item->ads->status,
                    "can_delete" => $can_delete,
                    "order_id_history" => $item->b2b_ads_id,
                    "volume" => (float)$item->price,
                    "start_date" => date("Y-m-d H:i:s", $item->start_date),
                    "end_date" => date("Y-m-d H:i:s", $item->end_date),
                    "status_history" => $item->status,
                    "description" => $item->ads->description
                ];
            }

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
                        $item->ads->amount = rtrim($item->ads->amount, '0');
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
                    
                    
                    $data[] = [
                        "b2b_ads_id" => $item->ads->id,
                        "uuid" => $item->ads->uuid,
                        "date" => date("Y-m-d H:i:s", $item->ads->date),
                        "company_id" => $item->ads->user->id,
                        "company" => $item->ads->company->name,
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
                        "author_id" => $item->author_id,
                        "author" => $item->author->name,
                        "author_bank" => $item->author->bank,
                        "author_bik" => $item->author->bik,
                        "author_rs" => $item->author->rs,
                        "author_ks" => $item->author->ks,
                        "author_phone" => $item->author->phone,
                        "image_author" => Url::to([$item->user->getImage()->getUrl("75x75")], "https"),
                        "creator" => $item->company->name,
                        "creator_bank" => $item->company->bank,
                        "creator_id" => $item->creator_id,
                        "creator_bik" => $item->company->bik,
                        "creator_rs" => $item->company->rs,
                        "creator_ks" => $item->company->ks,
                        "creator_phone" => $item->company->phone,
                        "image_creator" => Url::to([$item->ads->user->getImage()->getUrl("75x75")], "https"),
                        "status" => $item->ads->status,
                        "can_delete" => $can_delete,
                        "order_id_history" => $item->b2b_ads_id,
                        "volume" => (float)$item->price,
                        "start_date" => date("Y-m-d H:i:s", $item->start_date),
                        "end_date" => date("Y-m-d H:i:s", $item->end_date),
                        "status_history" => $item->status,
                        "description" => $item->ads->description
                    
                ];
            }
        }
            return $data;

    }


     /**
     * @SWG\Get(
     *    path = "/b2b/get-status-list",
     *    tags = {"b2b"},
     *    summary = "Список Статусов",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список компаний",
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

    

     protected function sendCode($company, $id, $email = null)
     {
         Yii::warning('Отправка уведомления об операции');
         $deal = B2bHistory::find()->where(['id' => $id])->one();
         if ($deal) {
             if ($email || $company->email) {
                 Yii::$app->mailer->compose()
                     ->setTo($company->email)
                     ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                     ->setSubject("Уведомление об операции")
                     ->setTextBody("Вашей компании поступило предложение по сделке № ".$deal->b2b_ads_id . ", на сумму: ".$deal->price . $deal->ads->chart->symbol)
                     ->send();
             }
         }
     }

}  
