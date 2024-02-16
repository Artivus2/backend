<?php

namespace app\modules\api\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\User;
use app\models\RatingsHistory;
use yii\db\ActiveQuery;



class RatingsController extends BaseController
{
 /**
     * @SWG\Get(
     *    path = "/ratings/info",
     *    tags = {"Ratings"},
     *    summary = "Информация о рейтинге пользователя",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="user_id",
     *	    in="path",
     *	    type="integer",
     *      description="ID пользователя",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Информация о рейтинге пользователя",
     *      @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/RatingsHistory")
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
    public function actionInfo()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $user_id = Yii::$app->request->get("user_id");

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }

        if(!$user_id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Пользователь не зарегистрирован"];
        }

        $ratingshistory = RatingsHistory::find(["user_id" => $user_id])->all();

        if(!$ratingshistory) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Нет информации по пользователю"];

        }
        
        $ratingsum = 0;
        $ratinghistorycount = 0;
        $data = [
            "user_id" =>$user_id,
            "FullRating" => $ratingsum,
            "CountRates" => $ratinghistorycount,
            "history" => []
        ];

        $ratinghistory = RatingsHistory::find()->where(["user_id" => $user_id])->all();
        $ratinghistorycount = RatingsHistory::find()->where(["user_id" => $user_id])->count();
        
        foreach ($ratinghistory as $h) {
            $ratingsum += $h->type / $ratinghistorycount;
            $data["history"][] = [
                "id" => $h->id,
                "type" => $h->type,
                "created_at" => $h->created_at,
                "description" => $h->description,
                "user_id_rater" => $h->user_id_rater,
                "login" => $h->user->login,
                "image_rater" => Url::to([$h->user->getImage()->getUrl("75x75")], "https"),
                "last_name" => $h->user->last_name,
                "first_name" => $h->user->first_name,
                "patronymic" => $h->user->patronymic

            ];
        }
        $data["FullRating"]=floor(100*$ratingsum)/100;
        $data["CountRates"]=(int)$ratinghistorycount;

        return $data;
    }


    /**
     * @SWG\Get(
     *    path = "/ratings/sort-ratings",
     *    tags = {"Ratings"},
     *    summary = "Общий список пользователей",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="sortingorder",
     *      in="path",
     *	    type="integer",
     *      description="По убыванию(1)/по возрастанию(0)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Сортированный список",
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
    public function actionSortRatings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        
        $keysort = Yii::$app->request->get("sortingorder");
        $direct = array(0,1);
        
	    if(!in_array($keysort, $direct)){
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Нет валидного типа сортировки", "sort"=>$keysort];

        } 

        if ($keysort==1) {
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("
            SELECT ratings_history.user_id, login, (AVG(type)) as Rating
            FROM ratings_history 
            LEFT JOIN user ON ratings_history.user_id = user.id 
            group by user_id
            order by Rating desc;");
    
            $result = $command->queryAll();
        } else {
            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("
            SELECT ratings_history.user_id, login, (AVG(type)) as Rating
            FROM ratings_history 
            LEFT JOIN user ON ratings_history.user_id = user.id 
            group by user_id
            order by Rating asc;");
    
            $result = $command->queryAll();
        }

        
        
        return $result;
    }



    /**
     * @SWG\Post(
     *    path = "/ratings/add-ratings",
     *    tags = {"Ratings"},
     *    summary = "Добавить значение к рейтингу пользователя",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="user_id",
     *      in="body",
     *      description="ID пользователя (кого оцениваем)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="body",
     *      description="Количество звезд (1-5)",
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="Комментарий к отзыву",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Обновление рейтинга для пользователя проведено",
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
    public function actionAddRatings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $rateduser = Yii::$app->request->post("user_id");
        if(!$rateduser) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не выбран пользователь для оценки"];
        }

        if(!Yii::$app->request->post("type")) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не выбранв оценка"];
        }


        $result = RatingsHistory::find()->where(["user_id" => $rateduser])->andWhere('created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)')->count();

        if($result > 0 ) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вы уже ставили оценку этому пользователю за последние 24 часа", "count" => $result, "id" => $rateduser];
        }

        if($rateduser == $this->user->id) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Себе нельзя оставлять отзыв"];
        }

        $data = Yii::$app->request->post();
        $ratingshistory = new RatingsHistory();
        $ratingshistory->user_id = Yii::$app->request->post("user_id");
        
        $ratingshistory->type = Yii::$app->request->post("type");
        $ratingshistory->created_at = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd H:i:s');
	$desc = Yii::$app->request->post("description");
	$desc = trim($desc);
	$desc = stripslashes($desc);
	$desc = htmlspecialchars($desc);
        $ratingshistory->description = $desc;
        $ratingshistory->user_id_rater = $this->user->id;
        if(!$ratingshistory->save()) {
            Yii::$app->response->statusCode = 400;
            return ["success" => false, "message" => "Не удалось сохранить отзыв", 'data'=>$data];

        };

        
        return ["success" => true, "message" => "Отзыв добавлен", 'data'=> $data];
    }


    

     /**
     * @SWG\Post(
     *    path = "/ratings/edit-ratings",
     *    tags = {"Ratings"},
     *    summary = "Редактировать рейтинг",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="user_id",
     *      in="body",
     *      description="ID пользователя (автор отзыва)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID отзыва",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="type",
     *      in="body",
     *      description="количество звезд (1-5)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="description",
     *      in="body",
     *      description="Комментарий отзыва",
     *      @SWG\Schema(type="string")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Редактирование рейтинга для пользователя проведено",
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
    public function actionEditRatings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $otzyv_id = Yii::$app->request->post("id");
        $rateduser = Yii::$app->request->post("user_id");
        $type = Yii::$app->request->post("type");
        $desc = Yii::$app->request->post("description");

        if(!$rateduser) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не выбран пользователь для оценки"];
        }

        if(!Yii::$app->request->post("type")) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Не выбрана оценка"];
        }

        $result = RatingsHistory::find()->where(["id" => $otzyv_id, "user_id" => $rateduser])->andWhere('created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)')->one();
        $resultcount = RatingsHistory::find()->where(["id" => $otzyv_id, "user_id" => $rateduser])->andWhere('created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)')->count();

        if(!$resultcount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Вы не можете редактировать этот отзыв, прошло более 12 часов"];
        } else {
            $result->type = $type;
            if ($desc) {
		$desc = trim($desc);
		$desc = stripslashes($desc);
		$desc = htmlspecialchars($desc);
                $result->description = $desc;
            }
            $result->created_at = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd H:i:s');
            $result->save();
            return ["success" => true, "message" => "Отзыв успешно изменен", "result" => $result];
            
        }
        
        

    }


      /**
     * @SWG\Delete(
     *    path = "/ratings/remove-ratings",
     *    tags = {"Ratings"},
     *    summary = "Удалить рейтинг",
     *    security={{"access_token":{}}},
     *    @SWG\Parameter(
     *      name="user_id",
     *      in="body",
     *      description="ID пользователя (автор отзыва)",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *    @SWG\Parameter(
     *      name="id",
     *      in="body",
     *      description="ID отзыва",
     *      required=true,
     *      @SWG\Schema(type="integer")
     *     ),
     *	  @SWG\Response(
     *      response = 200,
     *      description = "Удаление рейтинга для пользователя проведено",
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
    public function actionRemoveRatings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if(!$this->user) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Token не найден"];
        }
        $otzyv_id = Yii::$app->request->post("id");
        $rateduser = Yii::$app->request->post("user_id");

        $result = RatingsHistory::find()->where(["id" => $otzyv_id, "user_id" => $rateduser])->one();
        $resultcount = RatingsHistory::find()->where(["id" => $otzyv_id, "user_id" => $rateduser])->count();

        if(!$resultcount) {
            Yii::$app->response->statusCode = 401;
            return ["success" => false, "message" => "Отзыв не найден"];
        } else {
            $result->delete();
            return ["success" => true, "message" => "Отзыв успешно удален"];
            
        }


    }

    

}