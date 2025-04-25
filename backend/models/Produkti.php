<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "produkti".
 *
 * @property string $ProduktaNr
 * @property string|null $Apraksts
 * @property float|null $Tilpums
 * @property float|null $NetoSvars
 * @property string|null $IepakojumaTips
 * @property int $Izkartojums
 * @property int $PakasGarums
 * @property int $PakasPlatums
 * @property int $PakasAugstums
 * @property float|null $BruttoSvars
 * @property string|null $BazesMervieniba
 * @property string|null $PrecuBrends
 * @property int $ProduktiIepakojuma
 * @property int $ProduktiRinda
 * @property int $ProduktiPalete
 * @property int $RealizacijasTermins
 * @property string|null $ProduktaVeids
 * @property int $PudelesTips
 * @property int $barcode
 * @property string|null $ProduktaNosaukums
 * @property int $LinijasAtrums
 *
 * @property Paletes[] $paletes
 */
class Produkti extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'produkti';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProduktaNr', 'Izkartojums', 'PakasGarums', 'PakasPlatums', 'PakasAugstums', 'ProduktiIepakojuma', 'ProduktiRinda', 'ProduktiPalete', 'RealizacijasTermins', 'PudelesTips', 'barcode', 'ProduktaNosaukums', 'LinijasAtrums'], 'required'],
            [['Apraksts'], 'string'],
            [['Tilpums', 'NetoSvars', 'BruttoSvars'], 'number'],
            [['Izkartojums', 'PakasGarums', 'PakasPlatums', 'PakasAugstums', 'ProduktiIepakojuma', 'ProduktiRinda', 'ProduktiPalete', 'RealizacijasTermins', 'PudelesTips', 'barcode', 'LinijasAtrums'], 'integer'],
            [['ProduktaNr'], 'string', 'max' => 50],
            [['IepakojumaTips', 'BazesMervieniba', 'PrecuBrends', 'ProduktaVeids', 'ProduktaNosaukums'], 'string', 'max' => 255],
            [['ProduktaNr'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ProduktaNr' => 'Produkta Nr',
            'Apraksts' => 'Apraksts',
            'Tilpums' => 'Tilpums',
            'NetoSvars' => 'Neto Svars',
            'IepakojumaTips' => 'Iepakojuma Tips',
            'Izkartojums' => 'Izkartojums',
            'PakasGarums' => 'Pakas Garums',
            'PakasPlatums' => 'Pakas Platums',
            'PakasAugstums' => 'Pakas Augstums',
            'BruttoSvars' => 'Brutto Svars',
            'BazesMervieniba' => 'Bazes Mervieniba',
            'PrecuBrends' => 'Precu Brends',
            'ProduktiIepakojuma' => 'Produkti Iepakojuma',
            'ProduktiRinda' => 'Produkti Rinda',
            'ProduktiPalete' => 'Produkti Palete',
            'RealizacijasTermins' => 'Realizacijas Termins',
            'ProduktaVeids' => 'Produkta Veids',
            'PudelesTips' => 'Pudeles Tips',
            'barcode' => 'Svītru Kods',
            'ProduktaNosaukums' => 'Produkta Nosaukums',
            'LinijasAtrums' => 'LinijasAtrums',
            
        ];
    }

    /**
     * Gets query for [[Paletes]].
     *
     * @return \yii\db\ActiveQuery|PaletesQuery
     */
    public function getPaletes()
    {
        return $this->hasMany(Paletes::class, ['ProduktaNr' => 'ProduktaNr']);
    }

    /**
     * {@inheritdoc}
     * @return PaletesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PaletesQuery(get_called_class());
    }
}
