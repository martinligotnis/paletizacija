<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\ProduktiSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="produkti-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ProduktaNr') ?>

    <?= $form->field($model, 'Apraksts') ?>

    <?= $form->field($model, 'Tilpums') ?>

    <?= $form->field($model, 'NetoSvars') ?>

    <?= $form->field($model, 'IepakojumaTips') ?>

    <?php // echo $form->field($model, 'Izkartojums') ?>

    <?php // echo $form->field($model, 'PakasGarums') ?>

    <?php // echo $form->field($model, 'PakasPlatums') ?>

    <?php // echo $form->field($model, 'PakasAugstums') ?>

    <?php // echo $form->field($model, 'BruttoSvars') ?>

    <?php // echo $form->field($model, 'BazesMervieniba') ?>

    <?php // echo $form->field($model, 'PrecuBrends') ?>

    <?php // echo $form->field($model, 'ProduktiIepakojuma') ?>

    <?php // echo $form->field($model, 'ProduktiRinda') ?>

    <?php // echo $form->field($model, 'ProduktiPalete') ?>

    <?php // echo $form->field($model, 'RealizacijasTermins') ?>

    <?php // echo $form->field($model, 'ProduktaVeids') ?>
    <?php // echo $form->field($model, 'PudelesTips') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
