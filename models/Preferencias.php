<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "preferencias".
 *
 * @property int $id
 * @property int $tema_id
 * @property bool $promociones
 * @property bool $noticias
 * @property bool $resumen
 * @property bool $tour
 *
 * @property Temas $tema
 * @property UsuariosDatos[] $usuariosDatos
 */
class Preferencias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'preferencias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tema_id'], 'default', 'value' => null],
            [['tema_id'], 'integer'],
            [['promociones', 'noticias', 'resumen', 'tour'], 'boolean'],
            [['tema_id'], 'exist', 'skipOnError' => true, 'targetClass' => Temas::className(), 'targetAttribute' => ['tema_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tema_id' => 'Tema ID',
            'promociones' => 'Promociones',
            'noticias' => 'Noticias',
            'resumen' => 'Resumen',
            'tour' => 'Tour',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTema()
    {
        return $this->hasOne(Temas::className(), ['id' => 'tema_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuariosDatos()
    {
        return $this->hasMany(UsuariosDatos::className(), ['preferencias_id' => 'id']);
    }
}
