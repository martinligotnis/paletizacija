<?php

namespace backend\controllers;


use Yii;
use backend\models\Paletes;
use backend\models\PaletesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination = ['pageSize' => 100,];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
}
