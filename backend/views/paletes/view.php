<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Paletes $model */

$this->title = $model->DatumsLaiks;
$this->params['breadcrumbs'][] = ['label' => 'Paletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="paletes-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'DatumsLaiks' => urlencode($model->DatumsLaiks)], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'DatumsLaiks' => $model->DatumsLaiks], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'ProduktaNr',
            'Apraksts',
            'ProduktiPalete',
            'DatumsLaiks',
            'PaletesID',
            'RealizacijasTermins',
            'IsPrinted',
        ],
    ]) ?>

</div>
