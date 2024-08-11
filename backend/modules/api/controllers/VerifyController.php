<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;;
use yii\web\UploadedFile;
use app\models\UserVerify;

class VerifyController extends BaseController
{

    /**
     * @SWG\Get(
     *    path = "/verify/list",
     *    tags = {"Verify"},
     *    summary = "Этапы прохождения верификации",
     *    security={{"access_token":{}}},
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Спиоск этапов верификации",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/UserVerify")
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

        $data = [];
        $verify = UserVerify::find()->where(["user_id" => $this->user->id])->all();
        if(count($verify) == 0) {
            for ($i = 0; $i <= 3; $i++) {
                $item = new UserVerify(["user_id" => $this->user->id, "type" => $i]);
                if($item->save()) {
                    $data[] = [
                        "type" => $i,
                        "status" => 0,
                        "comment" => null
                    ];
                }
            }
        } else {
            foreach ($verify as $v) {
                $data[] = [
                    "type" => $v->type,
                    "status" => $v->status,
                    "comment" => $v->comment
                ];
            }
        }

        return $data;
    }

    /**
     * @SWG\Post(
     *    path = "/verify/upload-photo",
     *    tags = {"Verify"},
     *    summary = "Отправить фото на верификацию",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="type",
     *      in="body",
     *      description="Тип верификации",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="image",
     *      in="body",
     *      description="Фото",
     *      required=true,
     *      @SWG\Schema(type="file")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Фото отправлено на проверку",
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
    public function actionUploadPhoto()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        $type = Yii::$app->request->post("type", 0);
        $verify = UserVerify::findOne(["user_id" => $this->user->id, "type" => $type, "status" => [0,2]]);
        if(!$verify) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Фотография уже в обработке"];
        }

        $image = UploadedFile::getInstanceByName('image');
        if($image->extension != "jpg" && $image->extension != "png" && $image->extension != "jpeg") {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Вы можете прикрепить jpg, png, jpeg"];
        }

        $path = 'yii2images/' . Yii::$app->security->generateRandomString() . '.' . $image->extension;
        $image->saveAs($path);

        $verify->status = 1;
        $verify->save();

        if($this->user->verify_status == 0 || $this->user->verify_status == 2) {
            $verify_count = UserVerify::find()->where(["user_id" => $this->user->id, "status" => [0, 2]])->count();
            if($verify_count == 0) {
                $this->user->verify_status = 1;
                $this->user->save();
            }
        }

        $verify->removeImages();
        $verify->attachImage($path, true);

        @unlink($path);

        return ["success" => true, "message" => "Фото отправлено на проверку"];;
    }

}
