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
            ['attribute'=>'date',  'label'=>'Date',  'format'=>['date','php:d.m.Y']],
            ['attribute'=>'day',   'label'=>'Day'],
            ['attribute'=>'product_no',    'label'=>'Product No'],
            ['attribute'=>'product_name',  'label'=>'Product Name'],
            ['attribute'=>'pallets_produced','label'=>'Pallets Produced'],
            ['attribute'=>'units_per_pallet','label'=>'Units/Pallet'],
            ['attribute'=>'line_speed_units_per_hour','label'=>'Line Speed (units/hr)'],
            ['attribute'=>'pallets_per_hour','label'=>'Pallets/hr'],
            [
            'attribute'=>'oee',
            'label'=>'Product OEE',
            'format'=>'percent',
            'value'=> function($model){ return $model['oee']; }
            ],
            [
            'attribute'=>'total_time',
            'label'=>'Total Production Time',
            'format'=>'text',
            ],
            [
            'attribute'=>'overall_efficiency',
            'label'=>'Overall Daily Efficiency',
            'format'=>'percent',
            'value'=>function($model){ return $model['overall_efficiency']; }
            ],
        ],
    ]); ?>


</div>
