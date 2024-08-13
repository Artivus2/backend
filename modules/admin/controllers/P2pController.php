<?php

namespace app\modules\admin\controllers;


use Yii;
use Exception;
use Throwable;
use DateTime;
use DateTimeZone;
use yii\web\Controller;
use app\models\Chart;
use app\models\Currency;
use app\models\User;
use app\models\Wallet;
use app\models\P2pAds;
use app\models\search\P2pSearch;
use app\models\P2pHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\PaymentUser;
use app\models\P2pPayment;
use app\models\StatusType;
use yii\data\ActiveDataProvider;
use app\models\MySubscribeCallback;
use app\modules\api\controllers\Assistant;
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
 * P2P controller for the `admin` module
 */
class P2pController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new P2pSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $history = P2pHistory::find()->where(['p2p_ads_id' => $model->id]);

        $p2ppayments = P2pPayment::find()->where(['p2p_ads_id' => $model->id]);
        
        $data = new ActiveDataProvider([
            'query' => $history,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        $payments = new ActiveDataProvider([
            'query' => $p2ppayments,
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
        $history = P2pHistory::find()->where(['p2p_ads_id' => $model->id]);

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
     * Finds the P2p model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = P2pAds::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelHistory($id)
    {
        if (($model = P2pHistory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatehistory($id)
    {
        $model = $this->findModelHistory($id);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
        $send =  new ChatMessage();
        //chat room
        $p2p_chat = P2pHistory::find()->where(['id' => $id])->one();

        if (!ChatRoom::find()->where(['id' => $p2p_chat->chat_room_id])->exists()) {
            throw new Exception(__FUNCTION__ . ". Нет чата с таким идентификатором $p2p_chat->chat_room_id");
        }
        $chat_database = new ChatDatabaseModel();

        try {
            //$messages = $chat_database->getMessagesByRoom($chat_room_id);
            $messages = $chat_database->getMessagesWithStatusesByRoomUser($p2p_chat->chat_room_id/*, $user_id*/);
            $warnings[] = __FUNCTION__ . '. Сообщения получены из БД';
//                $warnings[] = $messages;
        } catch (Throwable $exception) {
            $errors[] = __FUNCTION__ . '. Ошибка получения сообщений из БД';
            throw $exception;
        }

        

        


        if ($model->status == 5) {
            $p2p_ads = P2pAds::find()->where(['id' => $model->p2p_ads_id, 'status' => [-1,6]])->one();
            if (!$p2p_ads) {
                Yii::$app->response->statusCode = 401;
                return ["success" => false, "message" => "Ордер не найден"];
            }
            if ($p2p_ads->type == 2) {
                $p2p_h = P2pHistory::find()->where(['id' => $id, 'status' => $model->status])->one();
                if (!$p2p_h) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Сделка не найдена (в истории продаж)"];
                }
                //подтвердил оплату админ после выяснения
                if ($p2p_ads->amount == 0) {
                    $p2p_ads->status = 10;
                } else {
                    $p2p_ads->status = -1;
                    if ($p2p_ads->min_limit / $p2p_ads->course > $p2p_ads->amount) {
                        $p2p_ads->min_limit = $p2p_ads->amount * $p2p_ads->course;
                    }
                }
                $p2p_h->status = 4;
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
            if ($p2p_ads->type == 1) {
                $p2p_h = P2pHistory::find()->where(['id' => $id,  'status' => $model->status])->one();
                if (!$p2p_h) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Сделка не найдена (в истории покупок)"];
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
                $wallet_buyer = Wallet::find()->where(["user_id" => $p2p_h->creator_id, 'chart_id' => $p2p_ads->chart_id,'type' => 0])->one();
                if (!$wallet_buyer) {
                    $wallet_buyer = new Wallet(["user_id" => $p2p_h->creator_id, "chart_id" => $p2p_ads->chart_id,'type' => 0]);
                }
                $wallet_buyer->balance += $p2p_h->price;
                if(!$wallet_buyer->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения кошелька"];
                }

            }
            //history
            if(!$p2p_ads->save()) {
                Yii::$app->response->statusCode = 400;
                return ["success" => false, "message" => "Ошибка сохранения сделки"];
            }
            
        }

        //chat


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->p2p_ads_id]);
        }
        
        $attachment = null;
        $text = null;
        $chat_attachment_type_id = 0;
        if (Yii::$app->request->post()) {
            $sender_user_id = 631;
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

    public function actionSendmessage($id, $text)
    {
       
        //send_message
        $send = null;
        $attachment = null;
        $text = null;
        $sender_user_id = 631;
        $chat_attachment_type_id = 0;
        $chat_room_id = $id;
        //$current_date = Assistant::GetDateTimeNow();
        $time_zone = new DateTimeZone('Europe/Moscow');
        $now = DateTime::createFromFormat('U.u', sprintf('%.f', microtime(true)))->setTimeZone($time_zone);
        $new_message_id = $chat_database->newMessage($text, $sender_user_id, $chat_room_id, $now->format('Y-m-d H:i:s'), $chat_attachment_type_id, $attachment);
        return $this->render('updatehistory');
    }

    
    
}



