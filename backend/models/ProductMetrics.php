<?php
namespace backend\models;

use yii\db\ActiveRecord;

class ProductMetrics extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%product_metrics}}';
    }

    public function rules()
    {
        return [
            [['ProduktaNr','avg_interval_seconds','p25_interval_seconds','p75_interval_seconds','last_updated'], 'required'],
            [['avg_interval_seconds','p25_interval_seconds','p75_interval_seconds'], 'number'],
            [['last_updated'], 'safe'],
            [['ProduktaNr'], 'string', 'max' => 255],
            [['ProduktaNr'], 'unique'],
        ];
    }
}
