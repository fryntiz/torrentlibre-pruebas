<?php

namespace app\models;

use function md5_file;
use Yii;
use yii\db\Expression;
use yii\imagine\Image;

/**
 * This is the model class for table "torrents".
 *
 * @property int $id
 * @property int $licencia_id
 * @property int $categoria_id
 * @property int $usuario_id
 * @property string $titulo
 * @property string $resumen
 * @property string $descripcion
 * @property string $imagen
 * @property string $file
 * @property int $size
 * @property string $magnet
 * @property string $password
 * @property string $md5
 * @property int $n_descargas
 * @property bool $online
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Comentarios[] $comentarios
 * @property Puntos[] $puntos
 * @property Usuarios[] $usuarios
 * @property ReportesTorrents[] $reportesTorrents
 * @property Usuarios[] $usuarios0
 * @property Categorias $categoria
 * @property Licencias $licencia
 * @property Usuarios $usuario
 */
class Torrents extends \yii\db\ActiveRecord
{
    /**
     * Imagen subida mediante el formulario.
     * @var \yii\web\UploadedFile
     */
    public $u_img;

    /**
     * Archivo torrent subido mediante el formulario.
     * @var \yii\web\UploadedFile
     *
     */
    public $u_torrent;

    /**
     * @const ESCENARIO_CREATE Constante para cuando estamos insertando
     */
    const ESCENARIO_CREATE = 'create';

    /**
     * @const ESCENARIO_UPDATE Constante para cuando estamos actualizando
     */
    const ESCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'torrents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['licencia_id', 'categoria_id', 'usuario_id', 'titulo', 'resumen'], 'required'],
            [['licencia_id', 'categoria_id', 'usuario_id', 'size', 'n_descargas'], 'default', 'value' => null],
            [['licencia_id', 'categoria_id', 'usuario_id', 'size', 'n_descargas'], 'integer'],
            [['online'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['titulo', 'resumen', 'imagen', 'file', 'magnet', 'password', 'md5'], 'string', 'max' => 255],
            [['descripcion'], 'string', 'max' => 500],
            [['categoria_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categorias::className(), 'targetAttribute' => ['categoria_id' => 'id']],
            [['licencia_id'], 'exist', 'skipOnError' => true, 'targetClass' => Licencias::className(), 'targetAttribute' => ['licencia_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario_id' => 'id']],
            [['u_img'], 'file', 'extensions' => 'png, jpg'],
            [['u_torrent'], 'file', 'extensions' => 'torrent'],
            [
                ['u_torrent'], 'required', 'on' => self::ESCENARIO_CREATE,
                'message' => 'Es obligatorio agregar un Torrent válido'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'u_img',
            'u_torrent',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'licencia_id' => 'Licencia ID',
            'categoria_id' => 'Categoria ID',
            'usuario_id' => 'Usuario ID',
            'titulo' => 'Titulo',
            'resumen' => 'Resumen',
            'descripcion' => 'Descripcion',
            'imagen' => 'Imagen',
            'file' => 'File',
            'size' => 'Size',
            'magnet' => 'Magnet',
            'password' => 'Contraseña para Descomprimir',
            'md5' => 'Md5',
            'n_descargas' => 'N Descargas',
            'online' => 'Online',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'u_img' => 'Imagen Portada',
            'u_torrent' => 'Archivo Torrent',
        ];
    }

    /**
     * Sobreescribe el método personalizando la configuración
     * @return array Devuelve la configuración
     */
    public function behaviors()
    {
        return [
            // Creo un timestamp cada vez que salta el evento create
            // o update asignando el timestamp actual
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /**
     * Acciones llevadas a cabo antes de insertar un usuario
     * @param bool $insert Acción a realizar, si existe está insertando
     * @return bool Devuelve un booleano, si se lleva a cabo es true.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if ($this->scenario === self::ESCENARIO_UPDATE) {

                }
            }
            return true;
        }
        return false;
    }

    /**
     * Sube la imagen al directorio correspondiente y devuelve si fue posible.
     * El nombre se compone del "id-" seguido del nombre real de la imagen.
     * @return bool Indica si se lleva la acción
     */
    public function uploadImg()
    {
        if ($this->u_img === null) {
            return true;
        }
        $nombre = Yii::getAlias('@r_imgTorrent/') .
            $this->md5 . '-' .
            $this->u_img->baseName . '.' .
            $this->u_img->extension;
        $res = $this->u_img->saveAs($nombre);
        if ($res) {
            Image::thumbnail($nombre, 250, null)->save($nombre);
        }
        return $res;
    }

    /**
     * Sube la imagen al directorio correspondiente y devuelve si fue posible.
     * El nombre se compone del "id-" seguido del nombre real de la imagen.
     * @return bool Indica si se lleva la acción
     */
    public function uploadTorrent()
    {
        // Es obligatorio subir un torrent
        if ($this->u_torrent === null) {
            Yii::$app->session->setFlash('error', 'Es obligatorio el archivo torrent');
            return false;
        }

        $nombre = $this->md5 . '-' .
                  $this->u_torrent->baseName . '.' .
                  $this->u_torrent->extension;

        $rutaSave = Yii::getAlias('@r_torrents/') . $nombre;
        $res = $this->u_torrent->saveAs($rutaSave);
        return $res;
    }

    /*
    public function aumentarDescargas()
    {
        return
    }
    */

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['torrent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPuntos()
    {
        return $this->hasMany(Puntos::className(), ['torrent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuarios::className(), ['id' => 'usuario_id'])->viaTable('puntos', ['torrent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReportesTorrents()
    {
        return $this->hasMany(ReportesTorrents::className(), ['torrent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios0()
    {
        return $this->hasMany(Usuarios::className(), ['id' => 'usuario_id'])->viaTable('reportes_torrents', ['torrent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoria()
    {
        return $this->hasOne(Categorias::className(), ['id' => 'categoria_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicencia()
    {
        return $this->hasOne(Licencias::className(), ['id' => 'licencia_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id']);
    }

    /* ETIQUETAS BOOLEAN PARA GRIDVIEW */
    public function getOnlineLabel() {
        return $this->online ? 'Online' : 'Caido'; //? 'Si' : 'No';
    }
}
