<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\models\Affiliate;
use app\models\AffiliateHistory;
/**
 * Default controller for the `api` module
 */
class AffiliateController extends BaseController
{

    /**
     * @SWG\Get(
     *    path = "/affiliate/info",
     *    tags = {"Affiliate"},
     *    summary = "Информация по партнерской программе",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Информация по партнерской программе",
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
    public function actionInfo()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }


        $affiliate = Affiliate::findOne(["user_id" => $this->user->id]);
        if(!$affiliate) {
            $affiliate = new Affiliate(["user_id" => $this->user->id]);
            $affiliate->code = Yii::$app->security->generateRandomString(6);
            $affiliate->save();
        }

        $data = [
            "code" => $affiliate->code,
            "history" => []
        ];

        $history = AffiliateHistory::find()->where(["affiliate_id" => $affiliate->id])->all();
        foreach ($history as $h) {
            $data["history"][] = [
                "id" => $h->id,
                "name" => $h->user->login,
                "date" => date("Y-m-d H:i:s", $h->date)
            ];
        }
        return $data;
    }

}
