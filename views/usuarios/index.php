<?php

use app\assets\UsuariosAsset;
use yii\helpers\Html;
use yii\grid\GridView;
use app\helpers\Fechas;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;

// Registro assets para esta vista
UsuariosAsset::register($this);

?>
<div class="usuarios-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <h3>Usuarios Registrados</h3>

    <!-- Esta vista solo la puede ver el administrador -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'class' => 'grid-view',
        'tableOptions' => [
            'class' => 'tablaUsuariosIndex'
        ],
        'filterRowOptions' => [
            'class' => 'trSearch'
        ],
        'columns' => [
            'id',
            'nombre',
            'nick',
            'email:email',
            'web',
            'localidad',
            'provincia',
            'direccion',
            'telefono',
            //'fecha_nacimiento',
            [
                'attribute' => 'fecha_nacimiento',
                'format' => 'raw',
                'value' => function($model, $key, $index) {
                    return Fechas::calcularDiferencia($model['fecha_nacimiento']);
                }
            ],
            'geoloc',
            'sexo',
            'usuariosId.rol.tipo',  // Tipo de rol
            //'preferencias_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <!-- Esta vista la puede ver cualquier usuario registrado -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'class' => 'grid-view',
        'tableOptions' => [
            'class' => 'tablaUsuariosIndex'
        ],
        'filterRowOptions' => [
            'class' => 'trSearch'
        ],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'avatar',
                'format' => 'raw',
                'value' => function($model, $key, $index) {
                    $img = $model->avatar;
                    $ruta = yii::getAlias('@r_avatar').'/';

                    if ((! isset($img)) || (! file_exists($ruta.$img))) {
                        $img = 'default.png';
                    }

                    return '<img src="'.$ruta.$img.'" />';
                }
            ],

            'nick',
            'biografia',
            'twitter',
            //'avatar',

            //'password',
            //'auth_key',
            //'token',
            //'web',
            //'localidad',
            //'provincia',
            //'direccion',
            //'telefono',
            //'fecha_nacimiento',
            //'geoloc',
            //'sexo',
            //'preferencias_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
