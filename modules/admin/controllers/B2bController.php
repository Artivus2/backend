<?php

namespace app\modules\admin\controllers;


use Yii;
use Exception;
use yii\web\Controller;
use DateTime;
use DateTimeZone;
use app\models\Chart;
use app\models\Currency;
use app\models\Company;
use app\models\User;
use app\models\Wallet;
use app\models\B2bAds;
use app\models\search\B2bSearch;
use app\models\B2bHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\PaymentUser;
use app\models\B2bPayment;
use app\models\StatusType;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\models\chat\ChatCacheModel;
use app\models\chat\ChatDatabaseModel;
use app\models\chat\ChatMember;
use app\models\chat\ChatMessage;
use app\models\chat\ChatMessageFavorites;
use app\models\chat\ChatMessagePinned;
use app\models\chat\ChatMessageReciever;
use app\models\chat\ChatRecieverHistory;
use app\models\chat\ChatRoom;


/**
 * B2b controller for the `admin` module
 */
class B2bController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new B2bSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $history = B2bHistory::find()->where(['b2b_ads_id' => $model->id]);

        // $b2bpayments = Company::find()->where(['user_id' => $$model->user_id]);
        $b2bpayments = $this->findCompany($id);
        
        $data = new ActiveDataProvider([
            'query' => $history,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        
        $payments = new ActiveDataProvider([
            'query' => $b2bpayments,
            'pagination' => [
                'pageSize' => 10,
            ],
            ]);
        return $this->render('view', [
            'model' => $this->findModel($id),
            'history' => $data,
            'payments' => $payments
        ]);
    }


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $history = B2bHistory::find()->where(['b2b_ads_id' => $model->id]);

        $status = StatusType::find();
        
        $data = new ActiveDataProvider([
            'query' => $history,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        // $statuses = new ActiveDataProvider([
        //     'query' => $status,
        //     ]);

        return $this->render('update', [
            'model' => $model,
            'history' => $data,
            'status' => $status
        ]);
    }


    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


    
    public function actionDeletehistory($id)
    {
        $this->findModelHistory($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the B2b model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = B2bAds::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelHistory($id)
    {
        if (($model = B2bHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionFind($id)
    {
        $data = $this->findOkved($id);

        $model = new ActiveDataProvider([
            'query' => $data,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        
        if(!$model) {
            return "Данных не найдено";
        }

        return $this->render('find', [
            'model' => $data,
        ]);
//return json_encode($data);

    }


    protected function findCompany($id) {
        $b2b_ads = B2bAds::find()->where(['id' => $id])->one();
        if (!$b2b_ads){
            return null;
        }
        
        $company = B2bPayment::find()->where(['id' => $b2b_ads->id_rs]);
        if (!$company){
            return null;
        }

        return $company;


    }




    protected function findOkved($id)
    {
        
//        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $b2b_ads = B2bAds::find()->where(['id' => $id])->one();

        if (!$b2b_ads){
            return null;
        }
        
        $company = Company::find()->where(['user_id' => $b2b_ads->company_id])->one();
        if (!$company){
            return null;
        }

        $token = "add0edb65bd1fb92dc50a3da6f904e29d57f4775";
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->findById("party", $company->inn, 1);


        if(count($result) >= 1) {
            return [
                "inn" => $company->inn,
                "name" => $result[0]["value"]??null,
                "ogrn" => $result[0]["data"]["ogrn"]??null,
                "address" => $result[0]["data"]["address"]["value"]??null,
                "okved" => $result[0]["data"]["okved"]??null,
                "kpp" => $result[0]["data"]["kpp"]??null,
                "fio" => $result[0]["data"]["management"]["name"]??null,
                "phone" => $result[0]["data"]["phones"]["data"]["source"]??null
            ];
        } else {
            return [
                "inn" => null,
                "name" => null,
                "ogrn" => null,
                "address" => null,
                "kpp" => null,
                "fio" => null,
                "phone" => null
            ];
        }


        return $result;
    }

    public function actionUpdatehistory($id)
    {
        $model = $this->findModelHistory($id);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
        $send =  new ChatMessage();
        //chat room
        $b2b_chat = B2bHistory::find()->where(['id' => $id])->one();

        if (!ChatRoom::find()->where(['id' => $b2b_chat->chat_room_id])->exists()) {
            throw new Exception(__FUNCTION__ . ". Нет чата с таким идентификатором $b2b_chat->chat_room_id");
        }
        $chat_database = new ChatDatabaseModel();

        try {
            //$messages = $chat_database->getMessagesByRoom($chat_room_id);
            $messages = $chat_database->getMessagesWithStatusesByRoomUser($b2b_chat->chat_room_id/*, $user_id*/);
            $warnings[] = __FUNCTION__ . '. Сообщения получены из БД';
        } catch (Throwable $exception) {
            $errors[] = __FUNCTION__ . '. Ошибка получения сообщений из БД';
            throw $exception;
        }

        if ($model->status == 5) {
            $b2b_ads = B2bAds::find()->where(['id' => $model->b2b_ads_id, 'status' => [-1,6]])->one();
            if (!$b2b_ads) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Ордер не найден"];
            }
            if ($b2b_ads->type == 2) {
                $b2b_h = P2pHistory::find()->where(['id' => $id, 'status' => $model->status])->one();
                if (!$b2b_h) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Сделка не найдена (в истории продаж)"];
                }
                //подтвердил оплату админ после выяснения
                if ($b2b_ads->amount == 0) {
                    $b2b_ads->status = 10;
                } else {
                    $b2b_ads->status = -1;
                    if ($b2b_ads->min_limit / $b2b_ads->course > $b2b_ads->amount) {
                        $b2b_ads->min_limit = $b2b_ads->amount * $b2b_ads->course;
                    }
                }
                $b2b_h->status = 4;
                // $wallet_seller = Wallet::findOne(["user_id" => $p2p_h->author_id, 'chart_id' => $p2p_ads->chart_id,'type' => 0]);
                // if (!$wallet_seller) {
                //     $wallet_seller = new Wallet(["user_id" => $p2p_h->author_id, "chart_id" => $p2p_ads->chart_id, "type" => 0]);
                // }
                // $wallet_seller->balance += $p2p_h->price;
                // if(!$wallet_seller->save()) {
                //     Yii::$app->response->statusCode = 400;
                //     return ["success" => false, "message" => "Ошибка сохранения кошелька"];
                // }
            }

            //  typw1
            if ($b2b_ads->type == 1) {
                $b2b_h = B2bHistory::find()->where(['id' => $id,  'status' => $model->status])->one();
                if (!$b2b_h) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Сделка не найдена (в истории покупок)"];
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
                $wallet_buyer = Wallet::find()->where(["user_id" => $b2b_h->creator_id, 'chart_id' => $b2b_ads->chart_id,'type' => 1])->one();
                if (!$wallet_buyer) {
                    $wallet_buyer = new Wallet(["user_id" => $p2p_h->creator_id, "chart_id" => $p2p_ads->chart_id,'type' => 1]);
                }
                $wallet_buyer->balance += $b2b_h->price;
                if(!$wallet_buyer->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения кошелька"];
                }

            }
            //history
            if(!$b2b_ads->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения сделки"];
            }
            
        }

        //chat


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->b2b_ads_id]);
        }
        
        $attachment = null;
        $text = null;
        $chat_attachment_type_id = 0;
        if (Yii::$app->request->post()) {
            $sender_user_id = Yii::$app->params['chat_admin'];
            $text = $_POST["ChatMessage"]["primary_message"];
            $chat_room_id = $model->chat_room_id;
            //$current_date = Assistant::GetDateTimeNow();
            $time_zone = new DateTimeZone('Europe/Moscow');
            $now = DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)))->setTimeZone($time_zone);
            $send = $chat_database->newMessage($text, $sender_user_id, $chat_room_id, $now->format('Y-m-d H:i:s'), $chat_attachment_type_id, $attachment);
            return $this->refresh();
        }
        
        

        return $this->render('updatehistory', [
            'model' => $model,
            'messages' => $messages,
            'send' => $send
        ]);
    }

    public function actionAppeal()
    {
        
        $history = B2bHistory::find()->where(['status' => Yii::$app->params['appeal']]);
               
        $data = new ActiveDataProvider([
            'query' => $history,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        return $this->render('appeal', [
            'history' => $data,
        ]);
    }
}
