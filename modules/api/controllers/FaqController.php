<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\models\Faq;

class FaqController extends BaseController
{

    /**
     * @SWG\Post(
     *    path = "/faq/message",
     *    tags = {"FAQ"},
     *    summary = "Получить ответ на собщение",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="answer_id",
     *      in="body",
     *      description="ID ответа",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Успешно найдено",
     *      @SWG\Definition(
     *         required={"message", "answers"},
     *         @SWG\Property(
     *             property="message",
     *             type="string"
     *         ),
     *         @SWG\Property(
     *             property="answers",
     *             type="array",
     *             @SWG\Items(ref="#/definitions/Faq")
     *         )
     *      )
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
    public function actionMessage()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $data = [
            "message" => "Выберите вариант ответа",
            "answers" => []
        ];

        $answer_id = Yii::$app->request->post("answer_id", 0);

        if($answer_id != 0) {
            $message = Faq::findOne($answer_id);
            if(!$message) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ответ не найден"];
            }

            $data["message"] = $message->description;
        }

        $answers = Faq::find()->where(["parent_id" => $answer_id])->all();
        foreach ($answers as $answer) {
            $data["answers"][] = [
                "id" => $answer->id,
                "name" => $answer->title
            ];
        }

        return $data;
    }

}
