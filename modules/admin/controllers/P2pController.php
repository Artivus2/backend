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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
        $pnConfiguration = new PNConfiguration();
        // $pnConfiguration->setSubscribeKey("sub-c-7a080724-d4d0-46af-a644-53d651aa3dd4");
        // $pnConfiguration->setPublishKey("pub-c-ed0d5f65-4368-492b-a376-0b82917208b9");
        // $pnConfiguration->setUserId($model->author_id);
        // $pubnub = new PubNub($pnConfiguration);

        // $pubnub->publish([
        //     'channel' => 'chat-channel',
        //     'message' => $message,
        // ]);
        
        // return response()->json(['success' => true]);

        //return $pubnub;
    }
}
