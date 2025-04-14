<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Produkti $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="produkti-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ProduktaNr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Apraksts')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'Tilpums')->textInput() ?>

    <?= $form->field($model, 'NetoSvars')->textInput() ?>

    <?= $form->field($model, 'IepakojumaTips')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Izkartojums')->textInput() ?>

    <?= $form->field($model, 'PakasGarums')->textInput() ?>

    <?= $form->field($model, 'PakasPlatums')->textInput() ?>

    <?= $form->field($model, 'PakasAugstums')->textInput() ?>

    <?= $form->field($model, 'BruttoSvars')->textInput() ?>

    <?= $form->field($model, 'BazesMervieniba')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PrecuBrends')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ProduktiIepakojuma')->textInput() ?>

    <?= $form->field($model, 'ProduktiRinda')->textInput() ?>

    <?= $form->field($model, 'ProduktiPalete')->textInput() ?>

    <?= $form->field($model, 'RealizacijasTermins')->textInput() ?>

    <?= $form->field($model, 'ProduktaVeids')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'PudelesTips')->textInput() ?>

    <?= $form->field($model, 'barcode')->textInput() ?>
    
    <?= $form->field($model, 'ProduktaNosaukums')->textInput() ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
