<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paletes $model */

$this->title = 'Update Paletes: ' . $model->DatumsLaiks;
$this->params['breadcrumbs'][] = ['label' => 'Paletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->DatumsLaiks, 'url' => ['view', 'DatumsLaiks' => $model->DatumsLaiks]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="paletes-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
