<?php

namespace backend\controllers;


use Yii;
use backend\models\Paletes;
use backend\models\PaletesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\ProductMetrics;

/**
 * PaletesController implements the CRUD actions for Paletes model.
 */
class PaletesController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Paletes models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->mustBeLoggedIn();

        $searchModel = new PaletesSearch();

        $stats = $searchModel->getLastHourStats();
        $todaysStats = $searchModel->getTodayStats();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $dataProvider->pagination = ['pageSize' => 100,];

        $last200 = Paletes::find()
            ->orderBy(['DatumsLaiks'=>SORT_DESC])
            ->limit(200)
            ->select('DatumsLaiks')
            ->column();

        $metricsModels = ProductMetrics::find()->all();
        $metricsMap = [];
        foreach ($metricsModels as $m) {
            $metricsMap[$m->ProduktaNr] = $m;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'last200'      => $last200,
            'metricsMap'   => $metricsMap,
            'stats' => $stats,
            'todaysStats' => $todaysStats,
        ]);
    }
    
    /**
     * Displays a single Paletes model.
     * @param string $DatumsLaiks Datums Laiks
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($DatumsLaiks)
    {
        $this->mustBeLoggedIn();

        $DatumsLaiks = urldecode($DatumsLaiks);
        return $this->render('view', [
            'model' => $this->findModel($DatumsLaiks),
        ]);
    }

    /**
     * Creates a new Paletes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $this->mustBeLoggedIn();

        $model = new Paletes();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'DatumsLaiks' => $model->DatumsLaiks]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Paletes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $DatumsLaiks Datums Laiks
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($DatumsLaiks)
    {
        $this->mustBeLoggedIn();

        $DatumsLaiks = urldecode($DatumsLaiks);
        $model = $this->findModel($DatumsLaiks);
    
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'DatumsLaiks' => urlencode($model->DatumsLaiks)]);
        }
    
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Paletes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $DatumsLaiks Datums Laiks
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($DatumsLaiks)
    {
        $this->mustBeLoggedIn();

        $this->findModel($DatumsLaiks)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Paletes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $DatumsLaiks Datums Laiks
     * @return Paletes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($DatumsLaiks)
    {
        $this->mustBeLoggedIn();
        
        if (($model = Paletes::findOne(['DatumsLaiks' => $DatumsLaiks])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Checks if user is logged in, if not redirects to login page
     * Used to check in action methods
     * 
     * @return void 
     */
    protected function mustBeLoggedIn(){
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
    }
    
    /**
     * Recalculate production‐time metrics for each product
     */
    public function actionRecalcMetrics()
    {
        $k = 3;  // outlier multiplier—you can tweak (1.5 is typical for boxplot/IQR)
        $allProducts = Paletes::find()
            ->select('ProduktaNr')
            ->groupBy('ProduktaNr')
            ->column();

        foreach ($allProducts as $prodNr) {
            // 1) Determine full‐pallet size
            $fullSize = (int)Paletes::find()
                ->where(['ProduktaNr'=>$prodNr])
                ->max('ProduktiPalete');
            if ($fullSize <= 0) {
                continue;
            }

            // 2) Fetch full‐pallet timestamps
            $times = Paletes::find()
                ->where([
                    'ProduktaNr'    => $prodNr,
                    'ProduktiPalete'=> $fullSize,
                ])
                ->orderBy(['DatumsLaiks'=>SORT_ASC])
                ->select('DatumsLaiks')
                ->column();

            if (count($times) < 2) {
                continue;
            }

            // 3) Build raw intervals (in seconds)
            $raw = [];
            for ($i = 1, $n = count($times); $i < $n; $i++) {
                $raw[] = strtotime($times[$i]) - strtotime($times[$i-1]);
            }
            sort($raw);
            $n = count($raw);

            // 4) Compute initial percentiles and median
            $p25    = $raw[(int)floor(($n-1)*0.25)];
            $median = $raw[(int)floor(($n-1)*0.50)];
            $p75    = $raw[(int)floor(($n-1)*0.75)];
            $iqr    = $p75 - $p25;
            $threshold = $median + $k * $iqr;

            // 5) Filter out any “breakdown” intervals > threshold
            $filtered = array_filter($raw, function($x) use ($threshold) {
                return $x <= $threshold;
            });
            $filtered = array_values($filtered);
            $m = count($filtered);

            // 6) Decide: use filtered if still >=2 points, otherwise raw
            $use = ($m >= 2) ? $filtered : $raw;
            sort($use);
            $m = count($use);

            // 7) Recompute final metrics on $use
            $avg  = array_sum($use) / $m;
            $p25f = $use[(int)floor(($m-1)*0.25)];
            $p75f = $use[(int)floor(($m-1)*0.75)];

            // 8) Upsert into product_metrics
            $pm = ProductMetrics::findOne(['ProduktaNr'=>$prodNr]) 
                ?: new ProductMetrics(['ProduktaNr'=>$prodNr]);
            $pm->avg_interval_seconds = $avg;
            $pm->p25_interval_seconds = $p25f;
            $pm->p75_interval_seconds = $p75f;
            $pm->last_updated         = date('Y-m-d H:i:s');
            $pm->save(false);
        }

        Yii::$app->session->setFlash('success', 
            'Product metrics recalculated (outliers excluded).');
        return $this->redirect(['index']);
    }

}
