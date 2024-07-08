<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\models\Company;
use app\models\Okveds;
use app\models\Banks;
use app\models\B2bPayment;

class CompanyController extends BaseController
{

    /**
     * @SWG\Get(
     *    path = "/company/find",
     *    tags = {"Company"},
     *    summary = "Получение данных компании по ИНН",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="inn",
     *      in="path",
     *      type="string",
     *      description="ИНН компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
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
    public function actionFind()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $inn = Yii::$app->request->get("inn");

        $token = "de3f01177e5284d864f69417d3989ab1e4a098a6";
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->findById("party", $inn, 1);

        if(count($result) >= 1) {
            return [
                "name" => $result[0]["value"]??null,
                "ogrn" => $result[0]["data"]["ogrn"]??null,
                "address" => $result[0]["data"]["address"]["value"]??null,
                "okved" => $result[0]["data"]["okved"]??null,
                "kpp" => $result[0]["data"]["kpp"]??null,
                "fio" => $result[0]["data"]["management"]["name"]??null,
                "phone" => $result[0]["data"]["phones"]["data"]["source"]??null,
                "status" => $result[0]["data"]["state"]["status"]??null
            ];
        } else {
            return [
                "name" => null,
                "ogrn" => null,
                "address" => null,
            ];
        }
    }

    /**
     * @SWG\Get(
     *    path = "/company/list",
     *    tags = {"Company"},
     *    summary = "Список компаний пользователя",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="integer",
     *      description="ИД компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Список компаний",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Company")
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
        $id = Yii::$app->request->get("id");
        $data = [];
        if (!$id) {
        $company_query = Company::find()->where(["user_id" => $this->user->id])->all();
        } else {
        $company_query = Company::find()->where(["user_id" => $id])->all();
        }

        foreach ($company_query as $company) {
            
            $rss=[];
            $rs = B2bPayment::find()->where(['company_id' => $company->user_id, 'type' => 2])->all();
            
            if ($rs) {
                foreach ($rs as $item) {
                    $rss[] = [
                        "bank" => $item->bank,
                        "bik" => $item->bik,
                        "rs" => $item->value,
                        "ks" => $item->ks
                    ];
                }
            }
            $data[] = [
                "id" => $company->id,
                "name" => $company->name,
                "inn" => $company->inn,
                "ogrn" => $company->ogrn,
                "address" => $company->address,
                "main_okved" => $company->okved->okved_id ?? 'не указан',
                "kpp" => $company->kpp,
                "fio" => $company->fio,
                "phone" => $company->phone,
                "rs" => $rss
                // "bank" => $company->bankList->title,
                // "bik" => $company->bankList->bik,
                // "rs" => $company->rs,
                // "ks" => $company->bankList->ks
 
            ];
        }

        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/company/create",
     *    tags = {"Company"},
     *    summary = "Создать компанию",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="name",
     *      in="body",
     *      description="Название компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="address",
     *      in="body",
     *      description="Адрес компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="inn",
     *      in="body",
     *      description="Инн",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="ogrn",
     *      in="body",
     *      description="ОГРН",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="main_okved",
     *      in="body",
     *      description="ИД main ОКВЕД компании",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="status",
     *      in="body",
     *      required=true,
     *      description="Статус предприятия",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="kpp",
     *      in="body",
     *      description="КПП",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="fio",
     *      in="body",
     *      description="ФИО",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="phone",
     *      in="body",
     *      description="Телефон",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="bank",
     *      in="body",
     *      description="Банк",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="rs",
     *      in="body",
     *      description="Расчетный счет",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="ks",
     *      in="body",
     *      description="Корр счет",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="bik",
     *      in="body",
     *      description="БИК",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Компания создана",
     *      @SWG\Schema(ref = "#/definitions/Company")
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

        $inn = Yii::$app->request->post("inn");
        $company = Company::find()->where(['inn' => $inn])->all();
        if ($company) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Такая компания уже существует на платформе GREENAVI.COM"];
        }
        $name = Yii::$app->request->post("name");
        $ogrn = Yii::$app->request->post("ogrn");
        $address = Yii::$app->request->post("address");
        $kpp = Yii::$app->request->post("kpp");
        $fio = Yii::$app->request->post("fio");
        $phone = Yii::$app->request->post("phone");
        $main_okved = Yii::$app->request->post("main_okved", null);
        $status = Yii::$app->request->post("status");
        if ($status !== "ACTIVE") {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Статус компании не соответствует требованиям платформы GREENAVI.COM"];
        }
        $base_okved = Okveds::find()->filterwhere(['like','okved_id', $main_okved])->one();
        //$base_okved = Okveds::find()->where(['id' => $main_okved])->one();
        if (!$base_okved) {
            $main_okved->id = null;
        }
        // $bank = Yii::$app->request->post("bank");
        // $bik = Yii::$app->request->post("bik");
        // $rs = Yii::$app->request->post("rs");
        // $ks = Yii::$app->request->post("ks");

        if (!$inn || !$name || !$ogrn || !$address || !$kpp || !$fio) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не все обязательные реквизиты заполнены"];
        }

        

        $company = Company::find()->where(['user_id' => $this->user->id])->one();
        if (!$company) {
            $company = new Company(["user_id" => $this->user->id]);
            $company->name = Yii::$app->request->post("name");
            $company->inn = $inn;
            $company->ogrn = Yii::$app->request->post("ogrn");
            $company->address = Yii::$app->request->post("address");
            $company->kpp = Yii::$app->request->post("kpp");
            $company->fio = Yii::$app->request->post("fio");
            $company->phone = Yii::$app->request->post("phone");
            $company->main_okved = $base_okved->id;
            // $company->bank = Yii::$app->request->post("bank");
            // $company->bik = Yii::$app->request->post("bik");
            // $company->rs = Yii::$app->request->post("rs",'null');
            // $company->ks = Yii::$app->request->post("ks");
            if(!$company->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения компании"];
            }
        } else {
            Yii::$app->response->statusCode = 400;
            //return ["success" => false, "message" => "Для данного пользователя компания с указанным ИНН уже существует!"];
            return ["success" => false, "message" => "Для данного пользователя компания уже существует!"];
            }
        

        return ["success" => true, "message" => "Компания успешно добавлена"];
    }

    /**
     * @SWG\Post(
     *    path = "/company/update",
     *    tags = {"Company"},
     *    summary = "Обновить компанию",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID компании",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="inn",
     *      in="body",
     *      description="Инн",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="name",
     *      in="body",
     *      description="Название компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="ogrn",
     *      in="body",
     *      description="ОГРН",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="address",
     *      in="body",
     *      description="Адрес компании",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="main_okved",
     *      in="body",
     *      description="ОКВЕД",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="kpp",
     *      in="body",
     *      description="kpp",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="fio",
     *      in="body",
     *      description="ФИО руководителя",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="phone",
     *      in="body",
     *      description="Телефон",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="bank",
     *      in="body",
     *      description="Банк",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="bik",
     *      in="body",
     *      description="БИК",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="rs",
     *      in="body",
     *      description="Расчетный счет",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="ks",
     *      in="body",
     *      description="Корр счет",
     *      required=true,
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Изменения внесены",
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

        $company = Company::findOne(["user_id" => $this->user->id, "id" => Yii::$app->request->post("id")]);

        if ($company) {
            $company->name = Yii::$app->request->post("name") ?? $company->name;
            $company->inn = Yii::$app->request->post("inn") ?? $company->inn;
            $company->ogrn = Yii::$app->request->post("ogrn") ?? $company->ogrn;
            $company->address = Yii::$app->request->post("address") ?? $company->address;
            $company->main_okved = Yii::$app->request->post("main_okved") ?? $company->main_okved;
            $company->kpp = Yii::$app->request->post("kpp") ?? $company->kpp;
            $company->fio = Yii::$app->request->post("fio") ?? $company->fio;
            $company->phone = Yii::$app->request->post("phone") ?? $company->phone;
            // $company->bank = Yii::$app->request->post("bank") ?? $company->bank;
            // $company->bik = Yii::$app->request->post("bik") ?? $company->bik;
            // $company->rs = Yii::$app->request->post("rs") ?? $company->rs;
            // $company->ks = Yii::$app->request->post("ks") ?? $company->ks;

            if(!$company->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => $company->getErrors()];
            }

            return ["success" => true, "message" => "Компания успешно обновлена"];
        }

        return ["success" => false, "message" => "Компания не найдена"];
    }

    /**
     * @SWG\Post(
     *    path = "/company/delete",
     *    tags = {"Company"},
     *    summary = "Удалить компанию",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID компании",
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

        $payment = Company::findOne(['id' => Yii::$app->request->post("id"), 'user_id' => $this->user->id]);
        if(!$payment) {
            return ["success" => false, "message" => "Компания не найден"];
        }

        if(!$payment->delete()) {
            return ["success" => false, "message" => "Не удалось удалить компанию"];
        }

        return ["success" => true, "message" => "Компания успешно удалена"];
    }


    /**
     * @SWG\Get(
     *    path = "/company/get-okveds",
     *    tags = {"Company"},
     *    summary = "Получение актуального списка ОКВЕД",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="kod",
     *      in="path",
     *      type="string",
     *      description="код ОКВЕД",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="name",
     *      in="path",
     *      type="string",
     *      description="по наименованию ОКВЕД",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "ОКВЕДЫ",
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
    public function actionGetOkveds()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $kods = array();
        $kod = Yii::$app->request->get("kod");
        $name = Yii::$app->request->get("name");
        $kods = explode(",", $kod);
        if(!$kods) {
            $wherekods = ["IS NOT", "active", null];
        } else {
            $wherekods = ["like", "okved_id", $kods];
        }
        
        $names = array();
        $names = explode(",", $name);
        if(!$names) {
            $wherenames = ["IS NOT", "active", null];
        } else {
            $wherenames = ["like", "title", $names];
        }

        $result = Okveds::find()->where($wherekods)->andWhere($wherenames)->all();

        

        return $result;
    }


    /**
     * @SWG\Get(
     *    path = "/company/get-bank",
     *    tags = {"Company"},
     *    summary = "Список банков",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="id",
     *      in="path",
     *      type="string",
     *      required=false,
     *      description="поиск по ид банка",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="title",
     *      in="path",
     *      type="string",
     *      required=false,
     *      description="поиск по названию",
     *      @SWG\Schema(type="string")
     *     ),
     *    @SWG\Parameter(
     *      name="bik",
     *      in="path",
     *      type="string",
     *      required=false,
     *      description="поиск по БИК",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "БАНКИ",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/Banks")
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
    //https://www.cbr.ru/s/newbik сделать загрузку и парсер 
    //to do get-bik
     public function actionGetBank()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $id = Yii::$app->request->get("id");
        $title = Yii::$app->request->get("title");
        $bik = Yii::$app->request->get("bik");
        
        
        if(!$id) {
            $whereid = ["is not", "active", null];
        } else {
            $whereid = ['id' => $id];
        }
        
        if(!$title) {
            $wheretitle = ["<>", "title", ''];
        } else {
            $wheretitle = ["like", "title", $title];
        }
        if(!$bik) {
            $wherebik = ["<>", "title", ''];
        } else {
            $wherebik = ["like", "bik", $bik];
        }

        $result = Banks::find()
        ->where($whereid)
        ->andWhere($wheretitle)
        ->andwhere($wherebik)
        ->andwhere(["<>", "title", ''])
        ->all();

        

        return $result;
    }


    

}
