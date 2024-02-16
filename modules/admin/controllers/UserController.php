<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\models\UserVerify;
use app\models\Image;
use app\models\Wallet;
use app\models\search\UsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionWallet($id)
    {
        $model = $this->findModel($id);

        $wallet = Wallet::find()->where(['user_id' => $model->id]);
        

        if (!$wallet) {
            $data = 'Нет доступных кошельков на балансе';
        } else {
            $data = new ActiveDataProvider([
                'query' => $wallet,
                'pagination' => [
                    'pageSize' => 10,
              ],

                ]);
    
        }
        
        
        return $this->render('wallet', [
            'model' => $model,
            'wallet' => $data
        ]);
    }

    public function actionVerify($id)
    {
        $model = $this->findModel($id);

        $verify = Image::find()->where(['user_id' => $model->id]);
        

        if (!$verify) {
            $data = 'Нет доступных кошельков на балансе';
        } else {
            $data = new ActiveDataProvider([
                'query' => $verify,
                'pagination' => [
                    'pageSize' => 10,
              ],

                ]);
    
        }
        
        
        return $this->render('verify', [
            'model' => $model,
            'verify' => $data
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelWallet($id)
    {
        if (($model = Wallet::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findModelVerify($id)
    {
        if (($model = UserVerify::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionEditbalance($id)
    {
        $model = $this->findModelWallet($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        }

        return $this->render('editbalance', [
            'model' => $model,
        ]);

        
    }

    public function actionDeletebalance($id)
    {
        $this->findModelWallet($id)->delete();

        return $this->redirect(['index']);
    }
}
