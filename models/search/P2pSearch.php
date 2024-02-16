<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\P2pAds;
use app\models\Chart;
use app\models\Currency;
use app\models\Users;
use app\models\Wallet;
use app\models\P2pHistory;
use app\models\RatingsHistory;
use app\models\PaymentType;
use app\models\PaymentUser;
use app\models\P2pPayment;

/**
 * UsersSearch represents the model behind the search form of `app\models\Users`.
 */
class P2pSearch extends P2pAds
{
    public $balance;
    public $chart_id;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'user_id', 'chart_id', 'currency_id'], 'integer'],
            // ['status','in', 'range' => ['integer']],
            //[['status'], 'in', 'range' => [-1, 1, 2, 3,4,5,6,7,8,9,10], 'strict' => true],
            [['status'], 'checkRangeValid'],
            [['course', 'start_amount','amount','min_limit','max_limit'], 'number'],
            [['uuid', 'duration', 'date'], 'safe'],
        ];
    }

    public function checkRangeValid() {

        if (!$this->hasErrors()) {
    
            $statuses = !is_array($this->status) ? [$this->status] : $this->status;
    
            $allowed = [-1, 1, 2, 3 ,4 ,5,6,7,8,9,10]; // the allowed list
    
            foreach ($statuses as $status) {
    
                if (!in_array($status, $allowed)) {
    
                    $this->addError('status_id', "Invalid status '{$status}' in the list");
    
                    break;
    
                }
    
            }
    
        }
    
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
        $query = P2pAds::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'uuid',
                'type',
                'user_id',
                'chart_id',
                'currency_id',
                'date',
                'start_amount',
                'amount',
                'duration',
                'status'
                
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
                'user_id' => $this->user_id,
                //'user' => $this->user->login,
                //'first_name' => $this->user->first_name,
                //'last_name' => $this->user->last_name,
                'type' => $this->type,
                'chart_id' => $this->chart_id,
                'currency_id' => $this->currency_id,
                'date' => $this->date,
                'start_amount' => $this->start_amount,
                'amount' => $this->amount,
                'duration' => $this->duration,
                'status' => $this->status
        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'type', $this->type])    
            ->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['like', 'chart_id', $this->chart_id])
            ->andFilterWhere(['like', 'date', $this->date])
            ->andFilterWhere(['like', 'amount', $this->amount])
            ->andFilterWhere(['in', 'status', $this->status]);

        return $dataProvider;
    }
}
