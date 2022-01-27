<?php

namespace app\components\data;

use app\managers\LikeManager;
use app\models\Message;
use app\models\Photo;
use app\models\User;
use app\traits\managers\GuestManagerTrait;
use app\traits\managers\LikeManagerTrait;
use app\traits\SettingsTrait;
use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\imagine\Image;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\data
 */
abstract class BaseDataExport extends Component
{
    use GuestManagerTrait, LikeManagerTrait, SettingsTrait;

    /**
     * @var User
     */
    protected $user;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @return \app\models\Profile
     */
    protected function getProfile()
    {
        return $this->user->profile;
    }

    /**
     * @return Photo[]
     */
    protected function getPhotos()
    {
        return $this->user->photos;
    }

    /**
     * @return \app\models\Message[]|array
     */
    protected function getMessages()
    {
        return Message::find()
            ->whereTargetUser($this->user->id)
            ->joinWith(['sender', 'senderProfile', 'receiver', 'receiverProfile'])
            ->orderBy('id desc')
            ->all();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUserLikes()
    {
        $showIncomingLikes = false;
        $sitePremiumIncomingLikes = $this->settings->get('frontend', 'sitePremiumIncomingLikes', false);
        if ($sitePremiumIncomingLikes == false || ($sitePremiumIncomingLikes == true && $this->user->isPremium) || $this->user->isAdmin) {
            $showIncomingLikes = true;
        }

        return [
            LikeManager::TYPE_FROM_CURRENT_USER => $this->likeManager
                ->getUsersQuery(['type' => LikeManager::TYPE_FROM_CURRENT_USER, 'userId' => $this->user->id])->all(),
            LikeManager::TYPE_TO_CURRENT_USER =>
                $showIncomingLikes ?
                    $this->likeManager
                        ->getUsersQuery(['type' => LikeManager::TYPE_TO_CURRENT_USER, 'userId' => $this->user->id])->all()
                    :
                    [],
            LikeManager::TYPE_MUTUAL => $this->likeManager
                ->getUsersQuery(['type' => LikeManager::TYPE_MUTUAL, 'userId' => $this->user->id])->all(),
        ];
    }

    /**
     * @return \app\models\Guest[]|array
     */
    public function getGuests()
    {
        return $this->guestManager->getQuery()
            ->forUser($this->user->id)
            ->joinWith('fromUser')
            ->orderBy('created_at desc')
            ->all();
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @throws \yii\base\Exception
     */
    protected function initializeDirectory()
    {
        $this->workingDirectory = Yii::getAlias('@app/runtime/data-exports/' . time() . Yii::$app->security->generateRandomString(16));
        if (!is_dir($this->workingDirectory)) {
            FileHelper::createDirectory($this->workingDirectory);
        }
        FileHelper::createDirectory($this->workingDirectory . '/static');
        FileHelper::createDirectory($this->workingDirectory . '/media');
    }

    /**
     * @return bool
     */
    protected function createArchive()
    {
        if (!extension_loaded('zip')) {
            Yii::error('Zip extension must be enabled for data exporting feature');
            return false;
        }

        // add files to archive
        $zip = new \ZipArchive();
        $zipFile = Yii::getAlias('@app/runtime/data-exports/') . $this->filename;
        if (!$zip->open($zipFile, \ZipArchive::CREATE)) {
            return false;
        }

        $files = FileHelper::findFiles($this->workingDirectory, ['recursive' => true]);
        foreach ($files as $file) {
            $filePathRelative = str_replace($this->workingDirectory, '', $file);
            $zip->addFile($file, $filePathRelative);
        }


        $zip->close();

        return true;
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    protected function getUserPhotos()
    {
        $userPhotos = $this->getPhotos();
        $files = [];

        foreach ($userPhotos as $photo) {
            $files[] = $this->preparePhoto($photo);
            $photoSource = Yii::$app->photoStorage->getAbsolutePath($photo->source);
            $photoDestination = $this->workingDirectory . '/media/' . $photo->source;
            $thumbnailDestination = $this->workingDirectory . '/media/thumbnails/' . $photo->source;
            $photoDestinationDirectory = dirname($photoDestination);
            $thumbnailDestinationDirectory = dirname($thumbnailDestination);
            if (!is_dir($photoDestinationDirectory)) {
                FileHelper::createDirectory($photoDestinationDirectory);
            }
            if (!is_dir($thumbnailDestinationDirectory)) {
                FileHelper::createDirectory($thumbnailDestinationDirectory);
            }
            if (file_exists($photoSource)) {
                copy($photoSource, $photoDestination);
                Image::thumbnail($photoSource, 300, 150)
                    ->save($thumbnailDestination);
            }
        }

        return $files;
    }

    /**
     * @return bool
     */
    abstract public function create();

    /**
     * @return mixed|array
     */
    abstract public function getPages();

    /**
     * @param Photo $photo
     * @return mixed
     */
    abstract public function preparePhoto($photo);
}
