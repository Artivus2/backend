<?php

namespace app\modules\admin\controllers;


use Yii;
use yii\web\Controller;
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
        
        $company = Company::find()->where(['user_id' => $b2b_ads->company_id]);
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->b2b_ads_id]);
        }

        return $this->render('updatehistory', [
            'model' => $model,
        ]);
    }
}
