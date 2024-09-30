<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Produkti $model */

$this->title = 'Update Produkti: ' . $model->ProduktaNr;
$this->params['breadcrumbs'][] = ['label' => 'Produktis', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->ProduktaNr, 'url' => ['view', 'ProduktaNr' => $model->ProduktaNr]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="produkti-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
