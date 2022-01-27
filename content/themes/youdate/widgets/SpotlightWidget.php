<?php

namespace youdate\widgets;

use app\forms\SpotlightForm;
use app\models\Profile;
use app\models\Spotlight;
use app\models\User;
use app\traits\SettingsTrait;
use Yii;
use yii\base\Widget;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package youdate\widgets
 */
class SpotlightWidget extends Widget
{
    use SettingsTrait;

    /**
     * @var int
     */
    public $count;
    /**
     * @var Spotlight
     */
    public $spotlightUsers = null;
    /**
     * @var User
     */
    public $user;
    /**
     * @var Profile
     */
    public $profile;

    /**
     * @return string
     * @throws \Exception
     */
    public function run()
    {
        if ($this->spotlightUsers == null) {
            $this->spotlightUsers = Yii::$app->userManager->getSpotlightUsers($this->count);
        }

        $spotlightForm = new SpotlightForm();
        $spotlightForm->userId = $this->user->id;

        return $this->render('spotlight/horizontal', [
            'spotlightUsers' => $this->spotlightUsers,
            'user' => $this->user,
            'profile' => $this->profile,
            'spotlightForm' => $spotlightForm,
            'userPhotos' => $this->getUserPhotos(),
            'price' => Yii::$app->balanceManager->getSpotlightPrice(),
            'isPremiumFeaturesEnabled' => Yii::$app->balanceManager->isPremiumFeaturesEnabled(),
        ]);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getUserPhotos()
    {
        $requireVerification = $this->settings->get('common', 'photoModerationEnabled', false);
        $photos = [];
        foreach ($this->user->photos as $photo) {
            if (!$photo->is_verified && $requireVerification == true) {
                continue;
            }
            $photos[] = [
                'id' => $photo->id,
                'url' => $photo->getThumbnail(Profile::AVATAR_NORMAL, Profile::AVATAR_NORMAL)
            ];
        }

        return $photos;
    }
}
