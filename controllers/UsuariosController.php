<?php

/**
 * @author Raúl Caro Pastorino
 * @link http://www.fryntiz.es
 * @copyright Copyright (c) 2018 Raúl Caro Pastorino
 * @license https://www.gnu.org/licenses/gpl-3.0-standalone.html
**/

namespace app\controllers;

use app\models\Preferencias;
use app\models\UsuariosId;
use function var_dump;
use Yii;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * UsuariosController implements the CRUD actions for Usuarios model.
 */
class UsuariosController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],

            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'delete', 'update'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Devuelve todos los modelos de usuarios.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsuariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Usuarios model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Usuarios model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Usuarios(['scenario' => Usuarios::ESCENARIO_CREATE]);

        if ($model->load(Yii::$app->request->post())) {
            // Creo un nuevo id para este usuarios desde "usuarios_id"
            $usuario_id = new UsuariosId();

            // Creo nuevo id para preferencias_id desde "preferencias"
            $preferencias = new Preferencias(['tema_id' => 1]);
        }

        // Si entra mediante POST y puedo crear el usuario_id lo cargo al modelo
        if ($model->load(Yii::$app->request->post()) &&
            $usuario_id->save() &&
            $preferencias->save()
        ) {
            $model->id = $usuario_id->id;
            $model->preferencias_id = $preferencias->id;

            $model->imagen = UploadedFile::getInstance($model, 'imagen');

            if ($model->imagen !== null) {
                $model->avatar = $model->id . '-' .
                                 $model->imagen->baseName . '.' .
                                 $model->imagen->extension;
            }

            if ($model->save() && $model->upload()) {
                Yii::$app->user->login($model);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Usuarios model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id = 0)
    {
        $rol = Yii::$app->user->identity->rol;

        if ($rol !== 'admin') {
            $id = Yii::$app->user->id;
        }

        $model = $this->findModel($id);

        $model->scenario = Usuarios::ESCENARIO_UPDATE;
        $model->password = '';
        $model->password_repeat = '';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Usuarios model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id = 0)
    {
        $rol = Yii::$app->user->identity->rol;

        if ($rol === 'admin') {
            $model = $this->findModel($id)->delete();
        } else {
            $model = Yii::$app->user->identity->delete();
        }

        //$this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Usuarios model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Usuarios the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('No existe la página solicitada.');
    }


}
