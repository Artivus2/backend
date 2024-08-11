<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Chart;
use app\models\Currency;
use app\models\Users;
use app\models\Wallet;
use app\models\History;
use app\models\PaymentType;
use app\models\PaymentUser;

/**
 * UsersSearch represents the model behind the search form of `app\models\Users`.
 */
class HistorySearch extends History
{
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'type', 'end_price', 'end_chart_id', 'status'], 'integer'],
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
        $query = History::find();
        //$company = Company::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'type',
                'user_id',
                'end_price',
                'end_chart_id',
                'date',
                'payment_id',
                'status',
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
                'id' => $this->id,
                'type' => $this->type,
                'user_id' => $this->company_id,
                'end_chart_id' => $this->chart_id,
                'end_price' => $this->currency_id,
                'date' => $this->date,
                'status' => $this->status,

        ]);

        $query->andFilterWhere(['like', 'id', $this->uuid])
            ->andFilterWhere(['like', 'user_id', $this->company_id])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}
