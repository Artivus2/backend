<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UsersSearch represents the model behind the search form of `app\models\Users`.
 */
class UsersSearch extends User
{
    public $balance;
    public $chart_id;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'app_id', 'verify_status', 'is_admin', 'affiliate_invitation_id', 'deleted', 'banned'], 'integer'],
            [["balance", "chart_id"], "number"],
            [['uid', 'email', 'telegram', 'first_name', 'last_name', 'country', 'city', 'last_visit_time', 'created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find()->JoinWith("wallet");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'verify_status',
                'uid',
                'email',
                'first_name',
                'last_name',
                'country',
                'created_at',
                'balance' => [
                    'asc' => ['wallet.balance' => SORT_ASC],
                    'desc' => ['wallet.balance' => SORT_DESC],
                    'default' => SORT_ASC
                ],
                'chart_id' => [
                    'asc' => ['wallet.chart_id' => SORT_ASC],
                    'desc' => ['wallet.chart_id' => SORT_DESC],
                    'default' => SORT_ASC
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.id' => $this->id,
            'balance' => $this->balance,
            'chart_id' => $this->chart_id,
            'app_id' => $this->app_id,
            'verify_status' => $this->verify_status,
            'is_admin' => $this->is_admin,
            'affiliate_invitation_id' => $this->affiliate_invitation_id,
            'deleted' => $this->deleted,
            'banned' => $this->banned,
            'last_visit_time' => $this->last_visit_time,
            'confirm_email' => $this->confirm_email,
            'confirm_reset_expire' => $this->confirm_reset_expire,
            'confirm_delete_expire' => $this->confirm_delete_expire,
            'delete_date' => $this->delete_date,
        ]);

        $query->andFilterWhere(['like', 'uid', $this->uid])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'telegram', $this->telegram])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'confirm_email_token', $this->confirm_email_token])
            ->andFilterWhere(['like', 'confirm_reset_token', $this->confirm_reset_token])
            ->andFilterWhere(['like', 'confirm_delete_token', $this->confirm_delete_token]);

        return $dataProvider;
    }
}
