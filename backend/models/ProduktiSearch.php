<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Produkti;

/**
 * ProduktiSearch represents the model behind the search form of `backend\models\Produkti`.
 */
class ProduktiSearch extends Produkti
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProduktaNr', 'Apraksts', 'IepakojumaTips', 'BazesMervieniba', 'PrecuBrends', 'ProduktaVeids', 'ProduktaNosaukums', 'LinijasAtrums'], 'safe'],
            [['Tilpums', 'NetoSvars', 'BruttoSvars'], 'number'],
            [['Izkartojums', 'PakasGarums', 'PakasPlatums', 'PakasAugstums', 'ProduktiIepakojuma', 'ProduktiRinda', 'ProduktiPalete', 'RealizacijasTermins', 'PudelesTips', 'barcode'], 'integer'],
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
        $query = Produkti::find();

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
            'Tilpums' => $this->Tilpums,
            'NetoSvars' => $this->NetoSvars,
            'Izkartojums' => $this->Izkartojums,
            'PakasGarums' => $this->PakasGarums,
            'PakasPlatums' => $this->PakasPlatums,
            'PakasAugstums' => $this->PakasAugstums,
            'BruttoSvars' => $this->BruttoSvars,
            'ProduktiIepakojuma' => $this->ProduktiIepakojuma,
            'ProduktiRinda' => $this->ProduktiRinda,
            'ProduktiPalete' => $this->ProduktiPalete,
            'RealizacijasTermins' => $this->RealizacijasTermins,
            'PudelesTips'=> $this->PudelesTips,
            'LinijasAtrums' => $this->LinijasAtrums,
        ]);

        $query->andFilterWhere(['like', 'ProduktaNr', $this->ProduktaNr])
            ->andFilterWhere(['like', 'Apraksts', $this->Apraksts])
            ->andFilterWhere(['like', 'IepakojumaTips', $this->IepakojumaTips])
            ->andFilterWhere(['like', 'BazesMervieniba', $this->BazesMervieniba])
            ->andFilterWhere(['like', 'PrecuBrends', $this->PrecuBrends])
            ->andFilterWhere(['like', 'ProduktaNosaukums', $this->ProduktaNosaukums])
            ->andFilterWhere(['like', 'barcode', $this->barcode])
            ->andFilterWhere(['like', 'ProduktaVeids', $this->ProduktaVeids])
            ->andFilterWhere(['like', 'LinijasAtrums', $this->LinijasAtrums]);

        return $dataProvider;
    }
}
