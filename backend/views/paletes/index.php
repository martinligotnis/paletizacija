<?php
use yii\helpers\Html;
use yii\grid\SerialColumn;
use yii\grid\ActionColumn;
use kartik\export\ExportMenu;
use backend\models\ProductMetrics;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel backend\models\PaletesSearch */
/* @var $last200 array */
/* @var $metricsMap ProductMetrics[] */

$this->title = 'Paletes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paletes-stats">
    <h3>Last Hour Statistics</h3>
    <table class="table table-bordered">
        <tr>
            <th>Total Pallets</th>
            <td><?= Html::encode($stats['total_count']) ?></td>
        </tr>
        <tr>
            <th>First Record</th>
            <td><?= Html::encode($stats['oldest_record']) ?></td>
        </tr>
        <tr>
            <th>Latest Record</th>
            <td><?= Html::encode($stats['newest_record']) ?></td>
        </tr>
        <tr>
            <th>Unique Products Today</th>
            <td><?= Html::encode($todaysStats['unique_products']) ?></td>
        </tr>
        <tr>
            <th>Total Pallets Produced Today</th>
            <td><?= Html::encode($todaysStats['total_count']) ?></td>
        </tr>
        <tr>
            <th>Average production speed pal/hour</th>
            <td><?= Html::encode($todaysStats['pallets_per_hour']) ?></td>
        </tr>
    </table>
    
</div>
<div class="paletes-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Izveidot paleti', ['create'], ['class'=>'btn btn-success']) ?>
        <?= Html::a('Recalculate Metrics', ['recalc-metrics'], ['class'=>'btn btn-primary']) ?>
    </p>

<?php
$gridColumns = [
    ['class'=>SerialColumn::class],
    'ProduktaNr',
    'Apraksts',
    'ProduktiPalete',
    'DatumsLaiks',
    'PaletesID',
    'RealizacijasTermins',
    'IsPrinted',

    [
        'label'=>'Time Since Previous',
        'format'=>'raw',
        'value'=> function($model) use ($last200, $metricsMap) {
            /** @var $model backend\models\Paletes */
            $sec = $model->timeSincePreviousSeconds;
            if ($sec === null) {
                return '';
            }
            // Only color if in last 200
            if (!in_array($model->DatumsLaiks, $last200, true)) {
                return gmdate('H:i:s', $sec);
            }
            // fetch metrics
            $m = $metricsMap[$model->ProduktaNr] ?? null;
            if ($m) {
                if ($sec <= $m->avg_interval_seconds) {
                    $c = 'green';
                } elseif ($sec <= $m->p75_interval_seconds) {
                    $c = 'orange';
                } else {
                    $c = 'red';
                }
            } else {
                $c = 'black';
            }
            return "<span style=\"color:{$c}\">" . gmdate('H:i:s', $sec) . "</span>";
        },
    ],

    ['class'=>ActionColumn::class,
     'urlCreator'=>function($action,$model,$key,$index){
         return ['/'.$this->context->id.'/'.$action,'DatumsLaiks'=>$model->DatumsLaiks];
     }
    ],
];
?>

<?= ExportMenu::widget([
    'dataProvider'=>$dataProvider,
    'columns'=>$gridColumns,
    'clearBuffers'=>true,
]) ?>

<?= \kartik\grid\GridView::widget([
    'dataProvider'=>$dataProvider,
    'filterModel'=>$searchModel,
    'columns'=>$gridColumns,
]) ?>

</div>
