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

        // Main SQL: group by date + product, join to produkti for names & line speed
        $sql = "
            SELECT
                DATE(p.DatumsLaiks) AS date,
                DAYNAME(p.DatumsLaiks) AS day,
                p.ProduktaNr AS product_no,
                pr.ProduktaNosaukums AS product_name,
                COUNT(*) AS pallets_produced,
                pr.ProduktiPalete AS units_per_pallet,
                pr.LinijasAtrums AS line_speed_units_per_hour,
                ROUND(
                  COUNT(*) 
                  / (TIMESTAMPDIFF(SECOND, MIN(p.DatumsLaiks), MAX(p.DatumsLaiks)) / 3600),
                  1
                ) AS pallets_per_hour,
                ROUND(
                  (COUNT(*) * pr.ProduktiPalete)
                  / (
                    pr.LinijasAtrums
                    * (TIMESTAMPDIFF(SECOND, MIN(p.DatumsLaiks), MAX(p.DatumsLaiks)) / 3600)
                  ),
                  3
                ) AS oee,
                '' AS commentary
            FROM {$palTable} p
            LEFT JOIN {$prodTable} pr
              ON pr.ProduktaNr = p.ProduktaNr
            GROUP BY
              DATE(p.DatumsLaiks),
              p.ProduktaNr
            ORDER BY
              date DESC,
              product_no
        ";

        // Count grouped rows for pagination
        $countSql = "
            SELECT COUNT(*) FROM (
              SELECT 1
              FROM {$palTable}
              GROUP BY DATE(DatumsLaiks), ProduktaNr
            ) t
        ";
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
                'defaultOrder' => ['date'=>SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
