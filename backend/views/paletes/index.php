<?php

use backend\models\Paletes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var backend\models\PaletesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Paletes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="paletes-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Izveidot paleti', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="paletes-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <div class="paletes-stats">
    <h3>Last Hour Statistics</h3>
    <table class="table table-bordered">
        <tr>
            <th>Total Pallets</th>
            <td><?= Html::encode($stats['total_count']) ?></td>
        </tr>
        <tr>
            <th>First Record</th>
            <td><?= Html::encode($stats['oldest_record']) ?></td>
        </tr>
        <tr>
            <th>Latest Record</th>
            <td><?= Html::encode($stats['newest_record']) ?></td>
        </tr>
        <tr>
            <th>Unique Products Today</th>
            <td><?= Html::encode($todaysStats['unique_products']) ?></td>
        </tr>
        <tr>
            <th>Total Pallets Produced Today</th>
            <td><?= Html::encode($todaysStats['total_count']) ?></td>
        </tr>
        <tr>
            <th>Average production speed pal/hour</th>
            <td><?= Html::encode($todaysStats['pallets_per_hour']) ?></td>
        </tr>
    </table>
    
</div>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php 
    $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],

        'ProduktaNr',
        'Apraksts',
        'ProduktiPalete',
        'DatumsLaiks',
        'PaletesID',
        'RealizacijasTermins',
        'IsPrinted',
        [
            'label' => 'Time Since Previous',
            'value' => function($model) {
                return $model->getTimeSincePrevious();
            },
        ],
        ['class' => 'yii\grid\ActionColumn', 

        'urlCreator' => function ($action, $model, $key, $index) {

            return Url::to([$action, 'DatumsLaiks' => $model->DatumsLaiks]);

        }

        ],
    ];

    // Renders a export dropdown menu
    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'clearBuffers' => true, //optional
    ]);    

    // You can choose to render your own GridView separately
    ?>
    <?= \kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns
    ]);?>
</div>  
