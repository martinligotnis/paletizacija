<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Produkti $model */

$this->title = 'Create Produkti';
$this->params['breadcrumbs'][] = ['label' => 'Produktis', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produkti-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
