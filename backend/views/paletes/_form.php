<?php

use backend\models\Produkti;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;

/** @var yii\web\View $this */
/** @var backend\models\Paletes $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="paletes-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ProduktaNr')->dropDownList(
        ArrayHelper::map(Produkti::find()->all(), 'ProduktaNr', 'Apraksts'),
        [
            'prompt' => 'Izvēlieties produktu',
            'onchange' => '
                var selectedApraksts = $(this).find("option:selected").text();
                $("#paletes-apraksts").val(selectedApraksts);
            ',
        ]
    );?>


    <?= $form->field($model, 'Apraksts')->hiddenInput()->label(false); ?>

    <?= $form->field($model,'DatumsLaiks')->widget(DateTimePicker::classname([
            'name' => 'date',
            'type' => DateTimePicker::TYPE_COMPONENT_APPEND,
            'options' => ['placeholder' => 'Izvēlieties datumu...'],
            'pickerIcon' => '<i class="fas fa-calendar-alt text-primary"></i>',
            'removeIcon' => '<i class="fas fa-trash text-danger"></i>',
            'pluginOptions' => [
                'language' => 'lv',
                'minuteStep' => '60',
                'minView' => '2',
                'maxView' => '2',
                //'startView' => '3',
                'format' => 'dd.mm.yyyy', // формат который будет передаваться в базу
                'autoclose' => true, //авто закрытие
                'weekStart' => 1, //с какого дня начинается неделя
                'startDate' => date('Ymd'), //дата ниже которой нельзя установить значение
                'todayBtn' => true, // выбрать сегодняшнюю дату
                'todayHighlight' => true, // подсветка сегодняшнего дня
            ]
        ]));?>

    <?= $form->field($model, 'ProduktiPalete')->textInput() ?>

    <?= $form->field($model, 'PaletesID')->textInput() ?>

    <?= $form->field($model, 'RealizacijasTermins')->textInput() ?>
    <?= $form->field($model, 'IsPrinted')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>