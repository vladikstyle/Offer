<?php

namespace app\managers;

use app\models\BalanceTransaction;
use app\models\Spotlight;
use app\models\UserBoost;
use app\models\UserPremium;
use app\payments\BoostTransaction;
use app\payments\PremiumTransaction;
use app\payments\SpotlightTransaction;
use app\traits\managers\UserManagerTrait;
use app\traits\SettingsTrait;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii2tech\balance\ManagerActiveRecord;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\managers
 */
class BalanceManager extends ManagerActiveRecord
{
    use UserManagerTrait, SettingsTrait;

    /**
     * @param $userId
     * @return ActiveDataProvider
     */
    public function getTransactionsProvider($userId)
    {
        $query = BalanceTransaction::find()
            ->where(['balance_transaction.user_id' => $userId])
            ->orderBy('id desc');

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * @param $userId
     * @return integer
     */
    public function getUserBalance($userId)
    {
        $balance = $this->calculateBalance($userId);
        if ($balance == null) {
            return 0;
        }
        return $balance;
    }

    /**
     * @param $userId
     * @param $neededCredits
     * @return bool
     */
    public function hasEnoughCredits($userId, $neededCredits)
    {
        $balance = $this->getUserBalance($userId);

        return $balance >= $neededCredits;
    }

    /**
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function boostUser($userId)
    {
        $user = $this->userManager->getUserById($userId);
        $boostPrice = $this->getBoostPrice();
        if (!$this->hasEnoughCredits($userId, $boostPrice)) {
            return false;
        }

        if ($this->isAlreadyBoosted($userId) && !$user->isPremium) {
            return false;
        }

        $userBoost = UserBoost::boostUser($userId, $this->getBoostDuration());
        $this->decrease(['user_id' => $userId], $boostPrice, [
            'class' => BoostTransaction::class,
            'boostedAt' => $userBoost->boosted_at,
            'boostedUntil' => $userBoost->boosted_until,
        ]);

        return true;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function isAlreadyBoosted($userId)
    {
        return UserBoost::find()
            ->where(['user_id' => $userId])
            ->andWhere('boosted_until > :timestamp', ['timestamp' => time()])
            ->count() >= 1;
    }

    /**
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function activatePremium($userId)
    {
        $premiumPrice = $this->getPremiumPrice();
        if (!$this->hasEnoughCredits($userId, $premiumPrice)) {
            return false;
        }

        $userPremium = UserPremium::activatePremium($userId, $this->getPremiumDuration());
        $this->decrease(['user_id' => $userId], $premiumPrice, [
            'class' => PremiumTransaction::class,
            'premiumAt' => $userPremium->created_at,
            'premiumUntil' => $userPremium->premium_until,
        ]);

        return true;
    }

    /**
     * @param $userId
     * @param $photoId
     * @param null $message
     * @return bool
     * @throws Exception
     */
    public function submitSpotlight($userId, $photoId, $message = null)
    {
        $spotlightPrice = $this->getSpotlightPrice();
        if (!$this->hasEnoughCredits($userId, $spotlightPrice)) {
            return false;
        }

        $spotlight = new Spotlight();
        $spotlight->user_id = $userId;
        $spotlight->photo_id = $photoId;
        $spotlight->message = $message;
        if (!$spotlight->save()) {
            throw new Exception('Could not save spotlight entry');
        }

        $this->decrease(['user_id' => $userId], $spotlightPrice, [
            'class' => SpotlightTransaction::class,
            'userId' => $userId,
            'photoId' => $photoId,
            'message' => $message,
        ]);

        return true;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function isPremiumFeaturesEnabled()
    {
        return $this->settings->get('frontend', 'sitePremiumFeaturesEnabled', true);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getBoostPrice()
    {
        if (!$this->isPremiumFeaturesEnabled()) {
            return 0;
        }

        return (int) $this->settings->get('common', 'priceBoost');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getBoostDuration()
    {
        return (int) $this->settings->get('common', 'boostDuration', 30);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getPremiumPrice()
    {
        return (int) $this->settings->get('common', 'pricePremium');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getPremiumDuration()
    {
        return (int) $this->settings->get('common', 'premiumDuration', 30);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getSpotlightPrice()
    {
        if (!$this->isPremiumFeaturesEnabled()) {
            return 0;
        }

        return (int) $this->settings->get('common', 'priceSpotlight');
    }
}
