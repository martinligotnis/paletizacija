<?php

namespace backend\controllers;

use Yii;
use backend\models\Produkti;
use backend\models\ProduktiSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProduktiController implements the CRUD actions for Produkti model.
 */
class ProduktiController extends Controller
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
     * Lists all Produkti models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->mustBeLoggedIn();

        $searchModel = new ProduktiSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination = ['pageSize' => 50,];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Produkti model.
     * @param string $ProduktaNr Produkta Nr
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($ProduktaNr)
    {
        $this->mustBeLoggedIn();

        return $this->render('view', [
            'model' => $this->findModel($ProduktaNr),
        ]);
    }

    /**
     * Creates a new Produkti model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $this->mustBeLoggedIn();

        $model = new Produkti();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'ProduktaNr' => $model->ProduktaNr]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Produkti model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $ProduktaNr Produkta Nr
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($ProduktaNr)
    {
        $this->mustBeLoggedIn();

        $model = $this->findModel($ProduktaNr);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'ProduktaNr' => $model->ProduktaNr]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Produkti model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $ProduktaNr Produkta Nr
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($ProduktaNr)
    {
        $this->mustBeLoggedIn();
        
        $this->findModel($ProduktaNr)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Produkti model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $ProduktaNr Produkta Nr
     * @return Produkti the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($ProduktaNr)
    {
        $this->mustBeLoggedIn();
        
        if (($model = Produkti::findOne(['ProduktaNr' => $ProduktaNr])) !== null) {
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
