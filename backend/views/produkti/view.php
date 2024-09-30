<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Produkti $model */

$this->title = $model->ProduktaNr;
$this->params['breadcrumbs'][] = ['label' => 'Produkti', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="produkti-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Atjaunot', ['update', 'ProduktaNr' => $model->ProduktaNr], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Izdzēst', ['delete', 'ProduktaNr' => $model->ProduktaNr], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Vai esat pārliecināts, ka gribat izdzēst šo produktu?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'ProduktaNr',
            'Apraksts:ntext',
            'Tilpums',
            'NetoSvars',
            'IepakojumaTips',
            'Izkartojums',
            'PakasGarums',
            'PakasPlatums',
            'PakasAugstums',
            'BruttoSvars',
            'BazesMervieniba',
            'PrecuBrends',
            'ProduktiIepakojuma',
            'ProduktiRinda',
            'ProduktiPalete',
            'RealizacijasTermins',
            'ProduktaVeids',
            'PudelesTips',
        ],
    ]) ?>

</div>
