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
      $db        = Yii::$app->db;
      $palTable  = Paletes::tableName();
      $prodTable = Produkti::tableName();
  
      // Fetch all relevant rows for the period (could add filters for date range)
      $paletes = (new \yii\db\Query())
          ->from($palTable)
          ->all();
  
      // Group by date and product
      $grouped = [];
      foreach ($paletes as $p) {
          $date = date('Y-m-d', strtotime($p['DatumsLaiks']));
          $prod = $p['ProduktaNr'];
          $grouped[$date][$prod][] = $p;
      }
  
      // Fetch product info
      $produkti = Produkti::find()->indexBy('ProduktaNr')->all();
  
      $rows = [];
      foreach ($grouped as $date => $prods) {
          foreach ($prods as $prodNr => $pallets) {
              usort($pallets, function($a, $b) {
                  return strtotime($a['DatumsLaiks']) <=> strtotime($b['DatumsLaiks']);
              });
  
              $unitsPerPallet = $produkti[$prodNr]->ProduktiPalete ?? 1;
              $lineSpeed = $produkti[$prodNr]->LinijasAtrums ?? 1;
              $productName = $produkti[$prodNr]->ProduktaNosaukums ?? '';
  
              $palletCount = count($pallets);
              $unitCount = $palletCount * $unitsPerPallet;
  
              // Calculate total production time, skipping gaps > 4h
              $prodTimeSec = 0;
              for ($i = 1; $i < $palletCount; $i++) {
                  $diff = strtotime($pallets[$i]['DatumsLaiks']) - strtotime($pallets[$i-1]['DatumsLaiks']);
                  if ($diff <= 4*3600) {
                      $prodTimeSec += $diff;
                  }
              }
              // If only one pallet, prodTimeSec = 0
  
              // OEE calculation (avoid division by zero)
              $prodTimeHr = $prodTimeSec / 3600;
              $oee = ($prodTimeHr > 0 && $lineSpeed > 0)
                  ? round($unitCount / ($lineSpeed * $prodTimeHr), 3)
                  : null;
  
              // Pallets/hour (for reference, using filtered time)
              $palletsPerHour = ($prodTimeHr > 0)
                  ? round($palletCount / $prodTimeHr, 1)
                  : null;
  
              $rows[] = [
                  'date' => $date,
                  'day' => date('l', strtotime($date)),
                  'product_no' => $prodNr,
                  'product_name' => $productName,
                  'pallets_produced' => $palletCount,
                  'units_per_pallet' => $unitsPerPallet,
                  'line_speed_units_per_hour' => $lineSpeed,
                  'pallets_per_hour' => $palletsPerHour,
                  'oee' => $oee,
                  'total_time' => gmdate('H:i:s', $prodTimeSec),
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
                  'pallets_produced','pallets_per_hour','oee','total_time'
              ],
              'defaultOrder' => ['date' => SORT_DESC],
          ],
      ]);
  
      return $this->render('index', [
          'dataProvider' => $dataProvider,
      ]);
  }
  
  
}
