<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\PaletesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="paletes-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ProduktaNr') ?>

    <?= $form->field($model, 'Apraksts') ?>

    <?= $form->field($model, 'ProduktiPalete') ?>

    <?= $form->field($model, 'DatumsLaiks') ?>

    <?= $form->field($model, 'PaletesID') ?>

    <?php // echo $form->field($model, 'RealizacijasTermins') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
