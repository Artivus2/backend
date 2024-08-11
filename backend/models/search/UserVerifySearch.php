<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Chart;
use app\models\Currency;
use app\models\UserVerify;

/**
 * UsersSearch represents the model behind the search form of `app\models\Users`.
 */
class UserVerifySearch extends UserVerify
{

    public function rules()
    {
        return [
            [['id', 'type', 'user_id', 'status'], 'integer'],
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
        $query = UserVerify::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'type',
                'user_id',
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
                'user_id' => $this->user_id,
                'type' => $this->type,
                'status' => $this->status
        ]);

        $query->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['in', 'status', $this->status]);

        return $dataProvider;
    }
}
