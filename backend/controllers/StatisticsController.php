<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\SqlDataProvider;
use backend\models\Paletes;
use backend\models\Produkti;

class StatisticsController extends Controller
{
    public function actionIndex()
    {
        $palTable  = \backend\models\Paletes::tableName();
        $prodTable = \backend\models\Produkti::tableName();

        // 1. Fetch all pallets for the period (e.g., last 30 days)
        $paletes = (new \yii\db\Query())
            ->from($palTable)
            ->orderBy(['DatumsLaiks' => SORT_ASC])
            ->all();

        // 2. Fetch product info
        $produkti = \backend\models\Produkti::find()->indexBy('ProduktaNr')->all();

        // 3. Group by date window (6AM to 6AM)
        $grouped = [];
        foreach ($paletes as $p) {
            $dt = strtotime($p['DatumsLaiks']);
            $dayStart = strtotime(date('Y-m-d 06:00:00', $dt));
            if ($dt < $dayStart) {
                // Before 6AM, belongs to previous day
                $dayStart = strtotime('-1 day', $dayStart);
            }
            $dayKey = date('Y-m-d', $dayStart);
            $grouped[$dayKey][] = $p;
        }

        $rows = [];
        foreach ($grouped as $dayKey => $pallets) {
            // 4. Identify production window for this day
            $windowStart = strtotime("$dayKey 06:00:00");
            $windowEnd = min(
                strtotime('+1 day', $windowStart), // next day 6:00
                strtotime(end($pallets)['DatumsLaiks'])
            );

            // 5. Split into product runs (consecutive same product)
            $runs = [];
            $currentRun = null;
            foreach ($pallets as $p) {
                if (!$currentRun || $currentRun['ProduktaNr'] !== $p['ProduktaNr']) {
                    if ($currentRun) $runs[] = $currentRun;
                    $currentRun = [
                        'ProduktaNr' => $p['ProduktaNr'],
                        'start' => strtotime($p['DatumsLaiks']),
                        'end' => strtotime($p['DatumsLaiks']),
                        'pallets' => [$p],
                    ];
                } else {
                    $currentRun['end'] = strtotime($p['DatumsLaiks']);
                    $currentRun['pallets'][] = $p;
                }
            }
            if ($currentRun) $runs[] = $currentRun;

            // 6. For each run, calculate metrics and cumulative efficiency
            $cumulativeUnits = 0;
            $cumulativeNominal = 0;
            $cumulativeTime = 0;
            $windowEndForCumulative = $windowStart;
            foreach ($runs as $i => $run) {
                $prodNr = $run['ProduktaNr'];
                $prod = $produkti[$prodNr] ?? null;
                $unitsPerPallet = $prod ? $prod->ProduktiPalete : 1;
                $lineSpeed = $prod ? $prod->LinijasAtrums : 1;
                $productName = $prod ? $prod->ProduktaNosaukums . " " . $prod->Tilpums . "L" : '';

                // Run time: from run start to run end or to window end, whichever is less
                $runStart = max($run['start'], $windowStart);
                $runEnd = min($run['end'], $windowEnd);
                $runTimeSec = $runEnd - $runStart;
                if ($runTimeSec < 0) $runTimeSec = 0;

                $palletCount = count($run['pallets']);
                $unitCount = $palletCount * $unitsPerPallet;

                // Nominal possible output for this run
                $runTimeHr = $runTimeSec / 3600;
                $nominalOutput = $lineSpeed * $runTimeHr;

                // Efficiency for this run
                $efficiency = ($nominalOutput > 0) ? ($unitCount / $nominalOutput) : null;

                // Cumulative: from windowStart to runEnd
                $cumulativeTime = $runEnd - $windowStart;
                $cumulativeUnits += $unitCount;
                $cumulativeNominal = $lineSpeed * ($cumulativeTime / 3600);

                $overallEfficiency = ($cumulativeNominal > 0) ? ($cumulativeUnits / $cumulativeNominal) : null;

                $rows[] = [
                    'date' => $dayKey,
                    'day' => date('l', strtotime($dayKey)),
                    'product_no' => $prodNr,
                    'product_name' => $productName,
                    'pallets_produced' => $palletCount,
                    'units_per_pallet' => $unitsPerPallet,
                    'line_speed_units_per_hour' => $lineSpeed,
                    'pallets_per_hour' => ($runTimeHr > 0) ? round($palletCount / $runTimeHr, 1) : null,
                    'oee' => $efficiency,
                    'total_time' => gmdate('H:i:s', $runTimeSec),
                    'overall_efficiency' => $overallEfficiency,
                ];
            }
        }

        // Use ArrayDataProvider for the view
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $rows,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'attributes' => [
                    'date','day','product_no','product_name',
                    'pallets_produced','pallets_per_hour','oee','total_time','overall_efficiency'
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    
    
  
  
}
