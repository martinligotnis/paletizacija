<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use backend\models\Paletes;
use backend\models\Produkti;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // 1. Determine today's production window from 06:00 → next 06:00
        $now      = new \DateTime();
        $dayStart = new \DateTime('today 06:00');
        if ($now < $dayStart) {
            $dayStart->modify('-1 day');
        }
        $dayStartStr = $dayStart->format('Y-m-d H:i:s');
        $nowStr      = $now->format('Y-m-d H:i:s');

        // --- First pallet time after 06:00 today ---
        $firstPalletTimeRaw = Paletes::find()
        ->where(['>=', 'DatumsLaiks', $dayStartStr])
        ->min('DatumsLaiks');

        // Format for display, or null if none produced yet
        $firstPalletTime = $firstPalletTimeRaw
            ? date('H:i:s', strtotime($firstPalletTimeRaw))
            : null;

        // 2. High‐level metrics
        $totalToday      = Paletes::find()
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->count();
        $uniqueProducts  = Paletes::find()
            ->select('ProduktaNr')
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->distinct()
            ->count();

        // First and last pallet times (for pallets/hr)
        $first = Paletes::find()
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->orderBy(['DatumsLaiks'=>SORT_ASC])
            ->one();
        $last  = Paletes::find()
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->orderBy(['DatumsLaiks'=>SORT_DESC])
            ->one();

        $palletsPerHour = 0;
        if ($first && $last && $first->DatumsLaiks !== $last->DatumsLaiks) {
            $hours = (strtotime($last->DatumsLaiks) - strtotime($first->DatumsLaiks)) / 3600;
            $palletsPerHour = $hours>0 ? round($totalToday/$hours, 1) : 0;
        }

        // 3. Overall OEE so far
        $prodInfo = ArrayHelper::index(
            Produkti::find()
                ->select([
                    'ProduktaNr',
                    'ProduktaNosaukums',
                    'Tilpums',
                    'IepakojumaTips',
                    'ProduktiPalete',
                    'LinijasAtrums',
                ])
                ->asArray()
                ->all(),
            'ProduktaNr'
        );
        $palToday = Paletes::find()
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->orderBy(['DatumsLaiks'=>SORT_ASC])
            ->all();

        $cumUnits    = 0;
        $maxLineSpeed= 0;
        foreach ($palToday as $p) {
            $info = $prodInfo[$p->ProduktaNr] ?? null;
            if ($info) {
                $cumUnits += $info['ProduktiPalete'];
                $maxLineSpeed = max($maxLineSpeed, $info['LinijasAtrums']);
            }
        }
        $elapsedHrs = (strtotime($nowStr) - strtotime($dayStartStr)) / 3600;
        $overallOee = ($maxLineSpeed>0 && $elapsedHrs>0)
            ? round($cumUnits / ($maxLineSpeed * $elapsedHrs) * 100, 1)
            : null;

        // 4. Current running product
        $lastPal = end($palToday);
        if ($lastPal) {
            $prod = $prodInfo[$lastPal->ProduktaNr] ?? null;
            if ($prod) {
                $currentProduct = $prod['ProduktaNosaukums']
                                . ' ' . $prod['Tilpums'] . 'l iepakots '
                                . $prod['IepakojumaTips'];
            } else {
                $currentProduct = 'Idle';
            }
        } else {
            $currentProduct = 'Idle';
        }

        // 5. Prepare hourly data for Chart.js
        $hourly = (new \yii\db\Query())
            ->select([
                'hour'  => new Expression('HOUR(DatumsLaiks)'),
                'count' => new Expression('COUNT(*)'),
            ])
            ->from(Paletes::tableName())
            ->where(['>=','DatumsLaiks',$dayStartStr])
            ->groupBy(new Expression('hour'))
            ->orderBy(new Expression('hour'))
            ->all();

        // Fill in missing hours with zero
        $counts = array_fill(0,24,0);
        foreach ($hourly as $h) {
            $counts[(int)$h['hour']] = (int)$h['count'];
        }

        // Render
        return $this->render('index', [
            'totalToday'     => $totalToday,
            'uniqueProducts' => $uniqueProducts,
            'palletsPerHour' => $palletsPerHour,
            'overallOee'     => $overallOee,
            'currentProduct' => $currentProduct,
            'firstPalletTime'=> $firstPalletTime, 
            'chartLabels'    => array_map(fn($h)=>sprintf('%02d:00',$h), range(0,23)),
            'chartData'      => array_values($counts),
        ]);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
