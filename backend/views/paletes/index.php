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

        ['class' => 'yii\grid\ActionColumn', 

        'urlCreator' => function ($action, $model, $key, $index) {

            return Url::to([$action, 'DatumsLaiks' => urlencode($model->DatumsLaiks)]);

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
