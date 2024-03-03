<?php

namespace app\modules\admin\controllers;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\History;
use app\models\Wallet;
use app\models\PaymentUser;
use app\models\B2bPayment;
use app\models\search\HistorySearch;

/**
 * BuyController implements the CRUD actions for Faq model.
 */
class BuyController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Faq models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $history = History::find()->Where(['in','wallet_direct_id', [11,12]]);
        //$searchModel = new HistorySearch();
        $dataProvider = new ActiveDataProvider([
            
            'query' => $history,
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            //'searchModel' => $searchModel
        ]);
    }

    /**
     * Displays a single Faq model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        //$payments = B2bPayment::find()->where(['p2p_ads_id' => $model->id]);
        $wallet = Wallet::find()->where(['user_id' => $model->id]);
        $payment = PaymentUser::find()->where(['user_id' => $model->user_id]);
        $b2bpayment = B2bPayment::find()->where(['company_id' => $model->user_id]);
        $wallets = new ActiveDataProvider([
            'query' => $wallet,
            'pagination' => [
                'pageSize' => 10,
          ],
            ]);
        $payments = new ActiveDataProvider([
            'query' => $payment,
            'pagination' => [
                'pageSize' => 10,
            ],
            ]);
        $b2bpayments = new ActiveDataProvider([
            'query' => $b2bpayment,
            'pagination' => [
                'pageSize' => 10,
            ],
            ]);

        return $this->render('view', [
            'model' => $model,
            'wallets' => $wallets,
            'payments' => $payments,
            'b2bpayments' => $b2bpayments
        ]);
    }


    /**
     * confirm offer for out.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionConfirm($id)
    {
        $model = $this->findModel($id);
        $model->status = 1;
        
        if ($model->wallet_direct_id == 10) {
            $wallet = Wallet::findOne(['user_id' => $model->user_id, 'chart_id' => $model->start_chart_id,'type' => 0]);
            }
        if ($model->wallet_direct_id == 13) {
            $wallet = Wallet::findOne(['user_id' => $model->user_id, 'chart_id' => $model->start_chart_id,'type' => 1]);
            }
        $wallet->blocked = 0; //учесть комиссию при возврате
        $model->save();
        $wallet->save();
        return $this->redirect(['index']);
    }

     /**
     * reject offer for out.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        //$wallet = Wallet::find()->where(['user_id' => $model->id]);
        $model->status = 2;
        if ($model->wallet_direct_id == 10) {
            $wallet = Wallet::findOne(['user_id' => $model->user_id, 'chart_id' => $model->start_chart_id,'type' => 0]);
            }
        if ($model->wallet_direct_id == 13) {
            $wallet = Wallet::findOne(['user_id' => $model->user_id, 'chart_id' => $model->start_chart_id,'type' => 1]);
            }
        $wallet->balance += $model->start_price;
        $wallet->blocked = 0;
        $model->save();
        $wallet->save();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Faq model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Faq the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = History::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelUser($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelWallet($id)
    {
        if (($model = Wallet::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
