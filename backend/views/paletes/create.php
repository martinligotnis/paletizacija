<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paletes $model */

$this->title = 'Create Paletes';
$this->params['breadcrumbs'][] = ['label' => 'Paletes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paletes-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
