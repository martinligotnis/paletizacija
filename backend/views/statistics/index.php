<?php
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */

$this->title = 'Production Statistics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="statistics-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute'=>'date',
                'label'=>'Date',
                'format'=>['date','php:d.m.Y'],
            ],
            [
                'attribute'=>'day',
                'label'=>'Day',
            ],
            [
                'attribute'=>'product_no',
                'label'=>'Product No',
            ],
            [
                'attribute'=>'product_name',
                'label'=>'Product Name',
            ],
            [
                'attribute'=>'pallets_produced',
                'label'=>'Pallets Produced',
            ],
            [
                'attribute'=>'units_per_pallet',
                'label'=>'Units/Pallet',
            ],
            [
                'attribute'=>'line_speed_units_per_hour',
                'label'=>'Line Speed (units/hr)',
            ],
            [
                'attribute'=>'pallets_per_hour',
                'label'=>'Pallets/hr',
            ],
            [
                'attribute'=>'oee',
                'label'=>'OEE',
                'format'=>'percent',
                'value'=>function($model){
                    // oee is stored as decimal fraction; e.g. 0.850 => 85.0%
                    return $model['oee'];
                }
            ],
            [
                'attribute'=>'commentary',
                'label'=>'Commentary',
            ],
        ],
    ]); ?>
</div>
