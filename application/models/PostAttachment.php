<?php

namespace app\models;

use Yii;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\models
 *
 * @property int $id
 * @property string $post_id
 * @property string $type
 * @property string $data
 *
 * @property Post $post
 */
class PostAttachment extends \app\base\ActiveRecord
{
    const TYPE_IMAGE = 'image';

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%post_attachment}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['post_id', 'type'], 'required'],
            [['post_id'], 'integer'],
            [['data'], 'string'],
            [['type'], 'string', 'max' => 32],
            [['post_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Post::class,
                'targetAttribute' => ['post_id' => 'id']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => Yii::t('app', 'Post'),
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Yii::$app->photoStorage->getUrl($this->data);
    }

    /**
     * @param $width
     * @param $height
     * @param string $fit
     * @param array $filters
     * @return bool|string
     * @throws \yii\base\InvalidConfigException
     */
    public function getThumbnail($width, $height, $fit = 'crop-center', $filters = [])
    {
        $params = array_merge([
            '/default/thumbnail', 'path' => $this->data,
            'id' => $this->id,
            'w' => $width, 'h' => $height, 'sharp' => 1, 'fit' => $fit,
        ], $filters);

        if (Yii::$app->glide->cachedFileExists($this->data, $params)) {
            return Yii::$app->glide->getCachedImage($this->data, $params);
        }

        $params = array_merge(['default/thumbnail'], $params);

        return Yii::$app->glide->createSignedUrl($params, true);
    }
}
