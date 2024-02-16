<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\B2bAds;
use app\models\Chart;
use app\models\Currency;
use app\models\Okveds;
use app\models\Company;
use app\models\Users;
use app\models\Wallet;
use app\models\B2bHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\PaymentUser;
use app\models\B2bPayment;

/**
 * UsersSearch represents the model behind the search form of `app\models\Users`.
 */
class B2bSearch extends B2bAds
{
    // public $chart_id;
    public $okved;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'company_id', 'chart_id', 'currency_id', 'status'], 'integer'],
            [['course', 'start_amount','amount','min_limit','max_limit'], 'number'],
            [['uuid', 'duration', 'date', 'main_okved'], 'safe'],
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
        $query = B2bAds::find();
        //$company = Company::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'uuid',
                'type',
                'company_id',
                'chart_id',
                'currency_id',
                'date',
                'start_amount',
                'amount',
                'status',
                'main_okved'

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
                'uuid' => $this->uuid,
                'type' => $this->type,
                'company_id' => $this->company_id,
                'chart_id' => $this->chart_id,
                'currency_id' => $this->currency_id,
                'date' => $this->date,
                'start_amount' => $this->start_amount,
                'amount' => $this->amount,
                'status' => $this->status,
                'main_okved' => $this->main_okved,
                

                
                

        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'company_id', $this->company_id])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'chart_id', $this->chart_id])
            ->andFilterWhere(['like', 'date', $this->date])
            ->andFilterWhere(['like', 'amount', $this->amount])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'main_okved', $this->main_okved]);

        return $dataProvider;
    }
}
