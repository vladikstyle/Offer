<?php

namespace app\components\data;

use Yii;
use yii\helpers\FileHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\data
 */
class HtmlDataExport extends BaseDataExport
{
    /**
     * @return bool
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function create()
    {
        $this->initializeDirectory();

        $previousPath = Yii::$app->assetManager->basePath;
        $previousUrl = Yii::$app->assetManager->baseUrl;
        $previousForceCopy = Yii::$app->assetManager->forceCopy;

        Yii::$app->assetManager->basePath = $this->workingDirectory . '/static';
        Yii::$app->assetManager->baseUrl = './static';
        Yii::$app->assetManager->forceCopy = true;

        $pages = $this->getPages();

        Yii::$app->view->params['userDisplayName'] = $this->user->profile->getDisplayName();

        foreach ($pages as $page => $params) {
            $params = array_merge($params, ['pages' => $pages]);
            $contents = Yii::$app->view->render('@app/views/data-export/' . $page, $params);
            file_put_contents($this->workingDirectory . "/$page.html", $contents);
        }

        Yii::$app->assetManager->basePath = $previousPath;
        Yii::$app->assetManager->baseUrl = $previousUrl;
        Yii::$app->assetManager->forceCopy = $previousForceCopy;

        $this->createArchive();
        FileHelper::removeDirectory($this->workingDirectory);

        return true;
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function getPages()
    {
        $userId = $this->user->id;

        return [
            'index' => [
                'excludeFromMenu' => true,
                'title' => $this->user->profile->getDisplayName(),
                'icon' => 'menu',
            ],
            'profile' => [
                'profile' => $this->getProfile(),
                'title' => Yii::t('app', 'Profile'),
                'icon' => 'user',
            ],
            'photos' => [
                'photos' => $this->getUserPhotos(),
                'title' => Yii::t('app', 'Photos'),
                'icon' => 'image',
            ],
            'balance' => [
                'balance' => Yii::$app->balanceManager->getUserBalance($userId),
                'transactionsProvider' => Yii::$app->balanceManager->getTransactionsProvider($userId),
                'title' => Yii::t('app', 'Balance'),
                'icon' => 'dollar-sign',
            ],
            'messages' => [
                'messages' => $this->getMessages(),
                'title' => Yii::t('app', 'Messages'),
                'icon' => 'message-square',
            ],
            'likes' => [
                'likes' => $this->getUserLikes(),
                'title' => Yii::t('app', 'Likes'),
                'icon' => 'heart',
            ],
            'guests' => [
                'guests' => $this->getGuests(),
                'title' => Yii::t('app', 'Guests'),
                'icon' => 'eye',
            ],
        ];
    }

    /**
     * @param \app\models\Photo $photo
     * @return array|mixed
     */
    public function preparePhoto($photo)
    {
        return [
            'model' => $photo,
            'url' => './media/' . $photo->source,
            'thumbnail' => './media/thumbnails/' . $photo->source,
        ];
    }
}
