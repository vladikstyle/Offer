<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\models\query\PhotoQuery;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property integer $id
 * @property integer $user_id
 * @property integer $width
 * @property integer $height
 * @property string $source
 * @property integer $is_verified
 * @property integer $is_private
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property float $ratio
 * @property User $user
 */
class Photo extends \app\base\ActiveRecord
{
    const VERIFIED = 1;
    const NOT_VERIFIED = 0;

    const PUBLIC = 0;
    const PRIVATE = 1;
    const PRIVATE_LOCKED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%photo}}';
    }

    /**
     * @inheritdoc
     * @return PhotoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PhotoQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];

        if (Yii::$app instanceof yii\web\Application) {
            $behaviors['blameable'] = [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ];
        }

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['is_verified'], 'boolean'],
            [['is_private'], 'in', 'range' => [self::PUBLIC, self::PRIVATE, self::PRIVATE_LOCKED]],
            [['source'], 'string', 'max' => 500],
            [['user_id'], 'integer'],
            [['width', 'height'], 'number', 'integerOnly' => true, 'min' => 1, 'max' => 10000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'User'),
            'source' => Yii::t('app', 'Source'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'is_verified' => Yii::t('app', 'Verified'),
            'is_private' => Yii::t('app', 'Private'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return null|string
     */
    public function getRatio()
    {
        if (isset($this->width) && isset($this->height) && $this->height != 0) {
            return sprintf('%.3f', $this->width / $this->height);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return Yii::$app->photoStorage->getUrl($this->source);
    }

    /**
     * @param $width
     * @param $height
     * @param string $fit
     * @param array $filters
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getThumbnail($width, $height, $fit = 'crop-center', $filters = [])
    {
        $params = array_merge([
            'id' => $this->id,
            'w' => $width, 'h' => $height, 'sharp' => 1, 'fit' => $fit,
        ], $filters);

        if (Yii::$app->glide->cachedFileExists($this->source, $params)) {
            return Yii::$app->glide->getCachedImage($this->source, $params);
        }

        $params = array_merge(['photo/thumbnail'], $params);

        return Yii::$app->glide->createSignedUrl($params, true);
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->is_private == self::PRIVATE_LOCKED || $this->is_private == self::PRIVATE;
    }

    /**
     * @param bool $save
     * @return bool
     */
    public function togglePrivate($save = false)
    {
        $this->is_private = !$this->is_private;
        if ($save) {
            return $this->save();
        }

        return true;
    }

    public function setPrivate($locked = false)
    {
        $this->is_private = $locked ? self::PRIVATE_LOCKED : self::PRIVATE;
    }

    public function makePublic()
    {
        $this->is_private = self::PUBLIC;
    }
}
