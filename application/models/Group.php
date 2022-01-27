<?php

namespace app\models;

use app\models\query\GroupQuery;
use app\models\query\GroupUserQuery;
use app\traits\EventTrait;
use app\traits\CountryTrait;
use trntv\filekit\behaviors\UploadBehavior;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property string $alias
 * @property int $is_verified
 * @property string $title
 * @property string $description
 * @property UploadedFile $photo
 * @property string $photo_path
 * @property UploadedFile $cover
 * @property string $cover_path
 * @property string $visibility
 * @property string $country
 * @property int $city
 * @property int $created_at
 * @property int $updated_at
 * @property int $members_count
 *
 * @property User $user
 * @property GroupUser[] $groupUsers
 * @property User[] $users
 */
class Group extends \app\base\ActiveRecord
{
    use EventTrait, CountryTrait;

    const VISIBILITY_VISIBLE = 'visible';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_BLOCKED = 'blocked';

    const EVENT_GET_PHOTO = 'getPhoto';
    const EVENT_GET_PHOTO_THUMBNAIL = 'getPhotoThumbnail';
    const EVENT_GET_COVER = 'getCover';
    const EVENT_GET_DISPLAY_NAME = 'getDisplayName';
    const EVENT_GET_DISPLAY_LOCATION = 'getDisplayLocation';
    const EVENT_GET_SHORT_DESCRIPTION = 'getShortDescription';

    /**
     * @var integer
     */
    public $membersCount;
    /**
     * @var UploadedFile
     */
    public $cover;
    /**
     * @var UploadedFile
     */
    public $photo;
    /**
     * @var null|string
     */
    protected $_displayLocation;

    /**
     * @return object|\yii\db\ActiveQuery|GroupQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(GroupQuery::class, [Group::class]);
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%group}}';
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
                'attribute' => 'title',
                'slugAttribute' => 'alias',
                'ensureUnique' => true,
                'immutable' => true,
            ],
            'cover' => [
                'class' => UploadBehavior::class,
                'filesStorage' => 'photoStorage',
                'attribute' => 'cover',
                'pathAttribute' => 'cover_path',
                'baseUrlAttribute' => false,
            ],
            'photo' => [
                'class' => UploadBehavior::class,
                'filesStorage' => 'photoStorage',
                'attribute' => 'photo',
                'pathAttribute' => 'photo_path',
                'baseUrlAttribute' => false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['is_verified'], 'boolean'],
            [['alias', 'title', 'description'], 'required'],
            [['alias'], 'string', 'max' => 128],
            [['visibility'], 'string', 'max' => 64],
            [['title', 'description'], 'string', 'max' => 255],
            [['members_count'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [['photo_path', 'cover_path'], 'string', 'max' => 500],
            [['photo', 'cover'], 'safe'],
            [['visibility'], 'default', 'value' => self::VISIBILITY_VISIBLE],
            [['visibility'], 'in',
                'range' => [self::VISIBILITY_VISIBLE, self::VISIBILITY_PRIVATE],
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],

            // country
            ['country', 'string', 'min' => 2, 'max' => 2],
            ['country', function ($attribute, $params) {
                return Yii::$app->geographer->isValidCountryCode($this->$attribute);
            }],

            // city
            ['city', 'integer', 'min' => 0],
            ['city', function ($attribute, $params) {
                return Yii::$app->geographer->isValidCityCode($this->$attribute);
            }],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_CREATE => [
                'title', 'description', 'visibility', 'cover', 'photo', 'country', 'city',
            ],
            self::SCENARIO_UPDATE => [
                'title', 'description', 'visibility', 'cover', 'photo', 'country', 'city',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'User'),
            'alias' => Yii::t('app', 'Alias'),
            'is_verified' => Yii::t('app', 'Verified'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'photo' => Yii::t('app', 'Photo'),
            'photo_path' => Yii::t('app', 'Photo'),
            'cover' => Yii::t('app', 'Cover photo'),
            'cover_path' => Yii::t('app', 'Cover photo'),
            'visibility' => Yii::t('app', 'Visibility'),
            'country' => Yii::t('app', 'Country'),
            'city' => Yii::t('app', 'City'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'members_count' => Yii::t('app', 'Members Count'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::class, ['group_id' => 'id']);
    }

    /**
     * @return int|string
     */
    public function getGroupUsersCount()
    {
        return $this->getGroupUsers()->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupPosts()
    {
        return $this->hasMany(GroupPost::class, ['group_id' => 'id']);
    }

    /**
     * @return int|string
     */
    public function getGroupPostsCount()
    {
        return $this->getGroupPosts()->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('group_user', ['group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])
            ->viaTable('group_post', ['group_id' => 'id']);
    }

    /**
     * @return mixed|null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhotoUrl()
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_PHOTO, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        return isset($this->photo) ? Yii::$app->photoStorage->getUrl($this->photo_path) : null;
    }

    /**
     * @param $width
     * @param $height
     * @param string $fit
     * @param array $filters
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhotoThumbnail($width, $height, $fit = 'crop-center', $filters = [])
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_PHOTO_THUMBNAIL, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        if (!isset($this->photo_path) || empty($this->photo_path)) {
            return null;
        }

        $params = array_merge([
            'id' => $this->id,
            'w' => $width, 'h' => $height, 'sharp' => 1, 'fit' => $fit
        ], $filters);

        if (Yii::$app->glide->cachedFileExists($this->photo_path, $params)) {
            return Yii::$app->glide->getCachedImage($this->photo_path, $params);
        }

        $params = array_merge(['group/thumbnail'], $params);

        return Yii::$app->glide->createSignedUrl($params, true);
    }

    /**
     * @return mixed|null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getCoverUrl()
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_COVER, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        return isset($this->cover_path) ? Yii::$app->photoStorage->getUrl($this->cover_path) : null;
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getDisplayTitle()
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_DISPLAY_NAME, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        return isset($this->title) ? trim($this->title) : null;
    }

    /**
     * @param int $wordsCount
     * @param bool $firstLineOnly
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getShortDescription($wordsCount = 40, $firstLineOnly = true)
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_SHORT_DESCRIPTION, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        $description = $this->description;
        if ($firstLineOnly) {
            $descriptionLines = explode("\n", $this->description);
            $description = $descriptionLines[0] ?? $this->description;
        }

        return StringHelper::truncateWords(trim($description), $wordsCount);
    }

    /**
     * @return mixed|null|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getDisplayLocation()
    {
        $event = $this->getGroupEvent($this);
        $this->trigger(self::EVENT_GET_DISPLAY_LOCATION, $event);
        if (isset($event->extraData)) {
            return $event->extraData;
        }

        if (!isset($this->_displayLocation)) {
            $this->_displayLocation = $this->getLocationString($this->country, $this->city);
        }

        return $this->_displayLocation;
    }

    /**
     * @return mixed|null
     */
    public function getCityName()
    {
        if (isset($this->city)) {
            return Yii::$app->geographer->getCityName($this->city);
        }

        return null;
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function updateMembersCount()
    {
        return $this->updateAttributes([
            'members_count' => GroupUser::find()->withoutBanned()->whereGroup($this)->count(),
        ]);
    }
}
