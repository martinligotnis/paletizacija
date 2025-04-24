<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "paletes".
 *
 * @property string $ProduktaNr
 * @property string $Apraksts
 * @property int $ProduktiPalete
 * @property string $DatumsLaiks
 * @property int $PaletesID
 * @property string $RealizacijasTermins
 * @property string $IsPrinted
 *
 * @property Produkti $produktaNr
 */
class Paletes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'paletes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ProduktaNr', 'Apraksts', 'ProduktiPalete', 'DatumsLaiks', 'PaletesID', 'RealizacijasTermins', 'IsPrinted'], 'required'],
            [['ProduktiPalete', 'PaletesID', 'IsPrinted'], 'integer'],
            [['DatumsLaiks', 'RealizacijasTermins'], 'safe'],
            [['ProduktaNr', 'Apraksts'], 'string', 'max' => 50],
            [['DatumsLaiks'], 'unique'],
            [['ProduktaNr'], 'exist', 'skipOnError' => true, 'targetClass' => Produkti::class, 'targetAttribute' => ['ProduktaNr' => 'ProduktaNr']],
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
            'ProduktiPalete' => 'Produkti Palete',
            'DatumsLaiks' => 'Datums Laiks',
            'PaletesID' => 'Paletes ID',
            'RealizacijasTermins' => 'Realizacijas Termins',
            'IsPrinted' => 'Is Printed',
        ];
    }

    /**
     * Gets query for [[ProduktaNr]].
     *
     * @return \yii\db\ActiveQuery|PaletesQuery
     */
    public function getProduktaNr()
    {
        return $this->hasOne(Produkti::class, ['ProduktaNr' => 'ProduktaNr']);
    }

    /**
     * {@inheritdoc}
     * @return PaletesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PaletesQuery(get_called_class());
    }

    /**
     * Returns the timestamp-difference (in seconds) between this pallet and the previous one.
     * @return int|null  seconds or null if none
     */
    public function getTimeSincePreviousSeconds()
    {
        $prev = self::find()
            ->andWhere(['<','DatumsLaiks',$this->DatumsLaiks])
            ->andWhere(['ProduktaNr'=>$this->ProduktaNr])
            ->orderBy(['DatumsLaiks'=>SORT_DESC])
            ->one();
        if (!$prev) {
            return null;
        }
        return strtotime($this->DatumsLaiks) - strtotime($prev->DatumsLaiks);
    }

    /**
     * Formats the above as H:i:s or returns null.
     */
    public function getTimeSincePrevious()
    {
        $sec = $this->timeSincePreviousSeconds;
        return $sec===null ? null : gmdate('H:i:s',$sec);
    }

}
