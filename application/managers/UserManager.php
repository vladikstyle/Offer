<?php

namespace app\managers;

use app\forms\UserSearchForm;
use app\helpers\Common;
use app\helpers\Url;
use app\models\Ban;
use app\models\Block;
use app\models\Encounter;
use app\models\Profile;
use app\models\ProfileField;
use app\models\query\UserQuery;
use app\models\Spotlight;
use app\models\User;
use app\traits\CacheTrait;
use app\traits\managers\PhotoManagerTrait;
use app\traits\RequestResponseTrait;
use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\managers
 */
class UserManager extends Component
{
    use CacheTrait, RequestResponseTrait, PhotoManagerTrait;

    /**
     * @param $username
     * @return User|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function getUser($username)
    {
        $query = $this->getQuery()
            ->andWhere(['username' => $username]);

        return $query->one();
    }

    /**
     * @param $id
     * @param array $params
     * @return User|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function getUserById($id, $params = [])
    {
        return $this->getQuery($params)
            ->andWhere(['user.id' => (int) $id])
            ->one();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function getUsersProvider($params)
    {
        /** @var User $currentUser */
        $currentUser = ArrayHelper::getValue($params, 'currentUser');
        $query = $this->getQuery();

        if (isset($params['searchForm']) && $params['searchForm'] instanceof UserSearchForm) {
            $userSearchForm = $params['searchForm'];
            if (isset($userSearchForm->sex)) {
                if ($userSearchForm->sex != Profile::SEX_NOT_SET) {
                    $query->andWhere('profile.sex & ' . (int) $userSearchForm->sex);
                }
            }
            if (isset($userSearchForm->fromAge)) {
                $query->andWhere('timestampdiff(year, dob, curdate()) >= :fromAge', [':fromAge' => $userSearchForm->fromAge]);
            }
            if (isset($userSearchForm->toAge)) {
                $query->andWhere('timestampdiff(year, dob, curdate()) <= :toAge', [':toAge' => $userSearchForm->toAge]);
            }
            if (isset($userSearchForm->locationType)) {
                switch ($userSearchForm->locationType) {
                    case UserSearchForm::LOCATION_TYPE_ADDRESS:
                        if (!empty($userSearchForm->country)) {
                            $query->andWhere(['profile.country' => $userSearchForm->country]);
                        }
                        if (!empty($userSearchForm->city)) {
                            $query->andWhere(['profile.city' => $userSearchForm->city]);
                        }
                        break;
                    case UserSearchForm::LOCATION_TYPE_NEAR:
                        if (!isset($userSearchForm->latitude) || !isset($userSearchForm->longitude)
                            || $userSearchForm->distance == UserSearchForm::DISTANCE_EVERYWHERE) {
                            break;
                        }
                        $query->locatedNear($userSearchForm->latitude, $userSearchForm->longitude, $userSearchForm->distance);
                        break;
                }
            }

            if ($userSearchForm->online) {
                $query->online();
            }
            if ($userSearchForm->verified) {
                $query->verified();
            }
            if ($userSearchForm->withPhoto) {
                $query->withPhoto();
            }

            /** @var ProfileField[] $profileFields */
            $profileFields = ArrayHelper::getValue($params, 'profileFields', []);
            $hasPremium = $currentUser == null ? false : $currentUser->isPremium;
            $searchInProfileFields = false;
            $subQuery = (new Query())->select('user_id')->from('profile_extra')->groupBy('user_id');

            foreach ($profileFields as $field) {
                if (!isset($userSearchForm->extraFields[$field->id])) {
                    continue;
                }
                if ($field->searchable_premium && !$hasPremium) {
                    continue;
                }
                $searchValue = ArrayHelper::getValue($userSearchForm->extraFields, $field->id, '');
                if ($searchValue !== '' && $field->getFieldInstance()->validateFieldValue($searchValue)) {
                    $searchInProfileFields = true;
                    $field->getFieldInstance()->applySearchQuery($subQuery, $searchValue);
                }
            }
            if ($searchInProfileFields && $subQuery->where !== null) {
                $subQuery->having('count(distinct field_id) = :searchKeysCount');
                $query->rightJoin(['profileExtra' => $subQuery], 'profileExtra.user_id = user.id');
            }
        }

        if (isset($params['hideCurrentUser'])) {
            $query->andWhere('user.id <> :user', [':user' => $currentUser->id]);
        }

        $query->orderBy(['user_boost.boosted_at' => SORT_DESC, 'user.id' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * @param $forUser
     * @param $limit
     * @param array $ignoreIds
     * @return array
     * @throws \Exception
     */
    public function getEncounters($forUser, $limit, $ignoreIds = [])
    {
        /** @var User[] $users */
        $query = $this->getQuery()
            ->andWhere(['<>', 'user.id', $forUser->id])
            ->join('left join', Encounter::tableName(),
                'encounter.to_user_id = user.id and encounter.from_user_id = :forUserId',
                ['forUserId' => $forUser->id])
            ->andWhere('encounter.id is null')
            ->andHaving('photosCount > 0')
            ->limit($limit);

        if (isset($forUser->profile->looking_for_sex)) {
            if ($forUser->profile->sex != Profile::SEX_NOT_SET) {
                $query->andWhere('profile.sex & ' . (int) $forUser->profile->looking_for_sex);
            }
        }
        if (isset($forUser->profile->looking_for_from_age)) {
            $query->andWhere('year(now()) - year(profile.dob) >= :fromAge', [
                ':fromAge' => $forUser->profile->looking_for_from_age,
            ]);
        }
        if (isset($forUser->profile->looking_for_to_age)) {
            $query->andWhere('year(now()) - year(profile.dob) <= :toAge', [
                ':toAge' => $forUser->profile->looking_for_to_age,
            ]);
        }

        if (isset($forUser->profile->latitude) && isset($forUser->profile->longitude)) {
            $query->locatedNear($forUser->profile->latitude, $forUser->profile->longitude);
            $query->orderBy('distance asc');
        }

        if (is_array($ignoreIds) && count($ignoreIds) < 10) {
            $query->andWhere(['not in', 'user.id', $ignoreIds]);
        }

        /** @var User[] $users */
        $users = $query->all();
        $encounters = [];
        $counter = 0;
        $sexOptions = $forUser->profile->getSexModels();

        foreach ($users as $user) {
            $encounters[$counter] = [
                'user' => [
                    'id' => $user->id,
                    'isOnline' => $user->isOnline,
                    'isPremium' => $user->isPremium,
                    'isVerified' => $user->profile->is_verified,
                ],
                'profile' => [
                    'url' => Url::to(['/profile/view', 'username' => $user->username]),
                    'avatar' => $user->profile->getAvatarUrl(96, 96),
                    'displayName' => $user->profile->getDisplayName(),
                    'displayLocation' => $user->profile->getDisplayLocation(),
                    'age' => $user->profile->getAge(),
                    'sex' => $user->profile->sex,
                    'sexAlias' => $sexOptions[$user->profile->sex]->alias ?? null,
                    'sexIcon' => $sexOptions[$user->profile->sex]->icon ?? null,
                    'sexTitle' => $user->profile->getSexTitle(),
                    'statusTitle' => $user->profile->getStatusTitle(),
                    'lookingForTitle' => $user->profile->getLookingForTitle(),
                    'lookingForAgeTitle' => $user->profile->getLookingForAgeTitle(),
                    'description' => $user->profile->description,
                ],
            ];
            foreach ($user->photos as $photo) {
                if (!$photo->isPrivate()) {
                    $encounters[$counter]['photos'][] = [
                        'id' => $photo->id,
                        'url' => $photo->getUrl(),
                    ];
                }
            }
            $counter++;
        }

        return $encounters;
    }

    /**
     * @param $fromUser User
     * @param $toUser User
     * @param bool $liked
     * @return Encounter
     */
    public function createEncounter($fromUser, $toUser, $liked = false)
    {
        $encounter = new Encounter();
        $encounter->from_user_id = $fromUser->id;
        $encounter->to_user_id = $toUser->id;
        $encounter->is_liked = $liked;
        if ($encounter->save()) {
            $encounter->refresh();
        }

        return $encounter;
    }

    /**
     * @param $count
     * @return Spotlight[]|array|\yii\db\ActiveRecord[]
     */
    public function getSpotlightUsers($count)
    {
        return Spotlight::find()
            ->joinWith(['user', 'user.profile', 'photo'])
            ->where(['is', 'blocked_at', null])
            ->orderBy('spotlight.created_at desc')
            ->limit($count)
            ->all();
    }

    /**
     * @param $userId
     * @param $blockedUserId
     * @return bool
     */
    public function blockUser($userId, $blockedUserId)
    {
        $block = new Block();
        $block->from_user_id = $userId;
        $block->blocked_user_id = $blockedUserId;

        return $block->save();
    }

    /**
     * @param $userId
     * @param $blockedUserId
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function unBlockUser($userId, $blockedUserId)
    {
        $model = Block::findOne([
            'from_user_id' => $userId,
            'blocked_user_id' => $blockedUserId,
        ]);

        if ($model == null) {
            return false;
        }

        return (bool) $model->delete();
    }

    /**
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]|Block[]
     */
    public function getBlockedUsers($userId)
    {
        return Block::find()
            ->where(['from_user_id' => $userId])
            ->with('blockedUser', 'blockedUser.profile')
            ->orderBy('block.id desc')
            ->all();
    }

    /**
     * @param $userId
     * @param $blockedUserId
     * @return bool
     */
    public function isUserBlocked($userId, $blockedUserId)
    {
        $block = Block::find()
            ->where(['from_user_id' => $userId, 'blocked_user_id' => $blockedUserId])
            ->one();

        return $block !== null;
    }

    /**
     * @param array $params
     * @return UserQuery|\yii\db\ActiveQuery
     * @throws \Exception
     */
    public function getQuery($params = [])
    {
        $verifiedPhotosOnly = $this->photoManager->isVerificationEnabled();
        $allPhotos = ArrayHelper::getValue($params, 'allPhotos', false);
        if ($allPhotos) {
            $verifiedPhotosOnly = false;
        }

        $query = User::find()
            ->select(['user.*', 'profile.*'])
            ->addSelect([
                'photosCount' => 'COUNT(photo.id)'
            ])
            ->leftJoin('photo as photoCount',
                'photoCount.user_id = user.id' . ($verifiedPhotosOnly ? ' and photoCount.is_verified = 1' : ''))
            ->joinWith(['profile', 'profile.photo', 'boost', 'premium'])
            ->groupBy(['user.id']);

        if (ArrayHelper::getValue($params, 'includeBanned', false) === false) {
            $query->andWhere(['is', 'blocked_at', null]);
        }

        return $query;
    }

    /**
     * @param null|string $ip
     * @param bool $disableCache
     * @return bool|mixed
     */
    public function checkBan($ip = null, $disableCache = false)
    {
        if ($ip === null) {
            $ip = $this->request->userIP;
        }

        $cacheKey = $this->banCacheKey($ip);
        $cached = $this->cache->get($this->banCacheKey($ip));
        if ($cached !== false && $disableCache !== true) {
            return $cached;
        }

        $ban = Ban::findOne(['ip' => $ip]);
        if ($ban !== null) {
            // exact ip match
            $this->cache->set($cacheKey, true, 600);
            return true;
        }

        $bans = Ban::find()->all();
        foreach ($bans as $ban) {
            if (Common::ipCIDRCheck($ip, $ban->ip)) {
                // ip in range/mask
                $this->cache->set($cacheKey, true, 600);
                return true;
            }
        }

        // no bans found
        $this->cache->set($cacheKey, false, 600);
        return false;
    }

    /**
     * @param Ban $ban
     * @return bool
     */
    public function createBan(Ban $ban)
    {
        if ($ban->save()) {
            $this->cache->set($this->banCacheKey($ban->ip), true, 600);
            return true;
        }

        return false;
    }

    /**
     * @param Ban $ban
     * @return bool
     */
    public function updateBan(Ban $ban)
    {
        $previousIP = $ban->oldAttributes['ip'];
        if ($ban->save()) {
            $this->cache->set($this->banCacheKey($ban->ip), true, 600);
            $this->cache->delete($this->banCacheKey($previousIP));
            return true;
        }

        return false;
    }

    /**
     * @param Ban $ban
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function removeBan(Ban $ban)
    {
        $this->cache->flush();
        return $ban->delete();
    }

    /**
     * @param $ip
     * @return int
     */
    public function removeBanByIP($ip)
    {
        $this->cache->flush();
        return Ban::deleteAll(['ip' => $ip]) > 0;
    }

    /**
     * @param $ip
     * @return string
     */
    protected function banCacheKey($ip)
    {
        return 'banStatus-' . $ip;
    }
}
