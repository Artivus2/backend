<?php

namespace app\modules\admin\controllers;


use Yii;
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
use PubNub\PubNub;
use PubNub\PNConfiguration;


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
                $wallet_seller = Wallet::findOne(["user_id" => $p2p_h->author_id, 'chart_id' => $p2p_ads->chart_id,'type' => 0]);
                if (!$wallet_seller) {
                    $wallet_seller = new Wallet(["user_id" => $p2p_h->author_id, "chart_id" => $p2p_ads->chart_id, "type" => 0]);
                }
                $wallet_seller->balance += $p2p_h->price;
                if(!$wallet_seller->save()) {
                    Yii::$app->response->statusCode = 400;
                    return ["success" => false, "message" => "Ошибка сохранения кошелька"];
                }
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
                $wallet_buyer = Wallet::findOne(["user_id" => $p2p_h->creator_id, 'chart_id' => $p2p_ads->chart_id,'type' => 0]);
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

        if ($model->load(Yii::$app->request->post()) && $model->save() && $model->status == 5) {
            return $this->redirect(['view', 'id' => $model->p2p_ads_id]);
        }

        return $this->render('updatehistory', [
            'model' => $model,
        ]);
    }


    
    public function actionChat($id)
    {
        
        $model = $this->findModelHistory($id);

        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        //     return $this->redirect(['view', 'id' => $model->p2p_ads_id]);
        // }

        // return $this->render('updatehistory', [
        //     'model' => $model,
        // ]);
        $pnconf = new PNConfiguration();

        $pnconf->setSubscribeKey("sub-c-7a080724-d4d0-46af-a644-53d651aa3dd4");
        $pnconf->setPublishKey("pub-c-ed0d5f65-4368-492b-a376-0b82917208b9");
        $pnconf->setSecure(false);
        $pnconf->setUserId("admin");
        $pubnub = new PubNub($pnconf);
        // $pnConfiguration->setSubscribeKey("sub-c-7a080724-d4d0-46af-a644-53d651aa3dd4");
        // $pnConfiguration->setPublishKey("pub-c-ed0d5f65-4368-492b-a376-0b82917208b9");
        // $pnConfiguration->setUserId($model->author_id);
        // $pubnub = new PubNub($pnConfiguration);
        
        $subscribeCallback = new MySubscribeCallback();
        $pubnub->addListener($subscribeCallback);


        // $result = $pubnub->subscribe()
        // ->channels("p2p_order_32024_07_22_14_49_12")
        // ->execute();


        $result = $pubnub->publish()
            ->channel("p2p_order_32024_07_22_14_49_12")
            ->message("Admin: 123")
            ->sync();

        return print_r($result);
        
        // return response()->json(['success' => true]); https://www.pubnub.com/docs/sdks/php#putting-it-all-together

        
    }
}



