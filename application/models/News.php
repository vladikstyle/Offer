<?php

namespace app\models;

use app\models\query\NewsQuery;
use hauntd\vote\behaviors\VoteBehavior;
use trntv\filekit\behaviors\UploadBehavior;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\HtmlPurifier;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 * @property int $id
 * @property int $user_id
 * @property bool $is_important
 * @property string $alias
 * @property string $status
 * @property string $title
 * @property string $excerpt
 * @property string $content
 * @property string $photo_source
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class News extends \app\base\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    /**
     * @var int
     */
    public $excerptWordsCount = 25;
    /**
     * @var array
     */
    public $contentPurifierConfig = [];
    /**
     * @var UploadedFile
     */
    public $photo;

    /**
     * @inheritdoc
     * @return NewsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NewsQuery(get_called_class());
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%news}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            'sluggable' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'alias',
                'attribute' => 'title',
                'ensureUnique' => true,
            ],
            'photo' => [
                'class' => UploadBehavior::class,
                'filesStorage' => 'photoStorage',
                'attribute' => 'photo',
                'pathAttribute' => 'photo_source',
                'baseUrlAttribute' => false,
            ],
            'vote' => [
                'class' => VoteBehavior::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['is_important'], 'boolean'],
            [['alias', 'title', 'photo_source'], 'string', 'max' => 255],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['excerpt', 'content'], 'string'],
            [['status'], 'string', 'max' => 32],
            [['status'], 'in', 'range' => array_keys($this->getStatusOptions())],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['photo'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'Author'),
            'status' => Yii::t('app', 'Status'),
            'is_important' => Yii::t('app', 'Important'),
            'title' => Yii::t('app', 'Title'),
            'excerpt' => Yii::t('app', 'Excerpt'),
            'content' => Yii::t('app', 'Content'),
            'photo_source' => Yii::t('app', 'Image'),
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
     * @return string
     */
    public function getContentPurified()
    {
        return HtmlPurifier::process($this->content, $this->contentPurifierConfig);
    }

    /**
     * @return string
     */
    public function getExcerpt()
    {
        if (!empty($this->excerpt)) {
            return $this->excerpt;
        }

        return StringHelper::truncateWords(strip_tags($this->content), $this->excerptWordsCount, '...', true);
    }

    /**
     * @return array
     */
    public function getStatusOptions()
    {
        return [
            self::STATUS_PUBLISHED => Yii::t('app', 'Published'),
            self::STATUS_DRAFT => Yii::t('app', 'Draft'),
        ];
    }

    /**
     * @return string|null
     */
    public function getPhotoUrl()
    {
        return Yii::$app->photoStorage->getUrl($this->photo_source);
    }

    /**
     * @return string
     */
    public function getPhotoPath()
    {
        return Yii::$app->photoStorage->getAbsolutePath($this->photo_source);
    }

    /**
     * @param $width
     * @param $height
     * @param string $fit
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhotoThumbnail($width, $height, $fit = 'crop-center')
    {
        return Yii::$app->glide->createSignedUrl([
            '/default/thumbnail', 'path' => $this->photo_source,
            'w' => $width,
            'h' => $height,
            'sharp' => 1,
            'fit' => $fit,
        ], true);
    }
}
