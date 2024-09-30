<?php

use backend\models\Produkti;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\ProduktiSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Produkti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produkti-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Izveidot Produktu', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'ProduktaNr',
            'Apraksts:ntext',
            'Tilpums',
            'NetoSvars',
            'IepakojumaTips',
            'Izkartojums',
            //'PakasGarums',
            //'PakasPlatums',
            //'PakasAugstums',
            //'BruttoSvars',
            //'BazesMervieniba',
            //'PrecuBrends',
            //'ProduktiIepakojuma',
            //'ProduktiRinda',
            //'ProduktiPalete',
            //'RealizacijasTermins',
            'ProduktaVeids',
            'PudelesTips',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Produkti $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'ProduktaNr' => $model->ProduktaNr]);
                 }
            ],
        ],
    ]); ?>


</div>
