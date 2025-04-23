<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Paletes;

/**
 * PaletesSearch represents the model behind the search form of `backend\models\Paletes`.
 */
class PaletesSearch extends Paletes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProduktaNr', 'Apraksts', 'DatumsLaiks', 'RealizacijasTermins'], 'safe'],
            [['ProduktiPalete', 'PaletesID'], 'integer'],
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
        $query = Paletes::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ProduktiPalete' => $this->ProduktiPalete,
            'DatumsLaiks' => $this->DatumsLaiks,
            'PaletesID' => $this->PaletesID,
            'RealizacijasTermins' => $this->RealizacijasTermins,
        ]);

        $query->andFilterWhere(['like', 'ProduktaNr', $this->ProduktaNr])
            ->andFilterWhere(['like', 'Apraksts', $this->Apraksts]);

        return $dataProvider;
    }
    public function getLastHourStats()
    {
        $query = Paletes::find()
            ->select([
                'COUNT(*) as total_count',
                'MIN(DatumsLaiks) as oldest_record',
                'MAX(DatumsLaiks) as newest_record',
                'COUNT(DISTINCT ProduktaNr) as unique_products'
            ])
            ->where('DatumsLaiks > DATE_SUB(NOW(), INTERVAL 1 HOUR)')
            ->andWhere(['IS NOT', 'DatumsLaiks', null]);
        
        return $query->asArray()->one();
    }
    public function getTodayStats()
    {
        return Paletes::find()
            ->select([
                'COUNT(*) as total_count',
                'MIN(DatumsLaiks) as first_pallet',
                'MAX(DatumsLaiks) as last_pallet',
                'COUNT(DISTINCT ProduktaNr) as unique_products',
                'CAST(COUNT(*) AS FLOAT) / (TIMESTAMPDIFF(SECOND, MIN(DatumsLaiks), MAX(DatumsLaiks)) / 3600.0) as pallets_per_hour'
            ])
            ->where('DATE(DatumsLaiks) = CURDATE()')
            ->asArray()
            ->one();
    }
}
