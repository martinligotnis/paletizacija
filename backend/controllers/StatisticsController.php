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

        // Main SQL: group by date+product, compute count+duration, then join produkti
        $sql = <<<SQL
SELECT
  stats.date,
  DAYNAME(stats.date)                        AS day,
  stats.product_no,
  pr.ProduktaNosaukums                       AS product_name,
  stats.pallets_produced,
  pr.ProduktiPalete                          AS units_per_pallet,
  pr.LinijasAtrums                           AS line_speed_units_per_hour,
  ROUND(
    stats.pallets_produced
    / (stats.duration_sec / 3600),
    1
  )                                           AS pallets_per_hour,
  ROUND(
    (stats.pallets_produced * pr.ProduktiPalete)
    / (pr.LinijasAtrums * (stats.duration_sec / 3600)),
    3
  )                                           AS oee,
  ''                                         AS commentary
FROM (
  SELECT
    DATE(DatumsLaiks)                        AS date,
    ProduktaNr                               AS product_no,
    COUNT(*)                                 AS pallets_produced,
    TIMESTAMPDIFF(
      SECOND,
      MIN(DatumsLaiks),
      MAX(DatumsLaiks)
    )                                         AS duration_sec
  FROM {$palTable}
  GROUP BY
    DATE(DatumsLaiks),
    ProduktaNr
) AS stats
LEFT JOIN {$prodTable} pr
  ON pr.ProduktaNr = stats.product_no
ORDER BY
  stats.date DESC,
  stats.product_no
SQL;

        // Count for pagination
        $countSql = <<<SQL
SELECT COUNT(*) FROM (
  SELECT 1
  FROM {$palTable}
  GROUP BY
    DATE(DatumsLaiks),
    ProduktaNr
) t
SQL;

        $totalCount = $db->createCommand($countSql)->queryScalar();

        $dataProvider = new SqlDataProvider([
            'sql'         => $sql,
            'totalCount'  => $totalCount,
            'pagination'  => ['pageSize' => 20],
            'sort'        => [
                'attributes'   => [
                  'date','day','product_no','product_name',
                  'pallets_produced','pallets_per_hour','oee'
                ],
                'defaultOrder' => ['date' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
