<?php

namespace app\components\data;

use app\helpers\Url;
use app\managers\LikeManager;
use app\models\BalanceTransaction;
use app\models\Guest;
use app\models\Like;
use app\models\Message;
use app\models\Profile;
use app\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\components\data
 */
class JsonDataExport extends BaseDataExport
{
    /**
     * @return bool
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function create()
    {
        $this->initializeDirectory();

        $pages = $this->getPages();
        foreach ($pages as $page => $params) {
            file_put_contents($this->workingDirectory . "/$page.json", json_encode($params, 128));
        }

        $this->createArchive();
        FileHelper::removeDirectory($this->workingDirectory);

        return true;
    }

    /**
     * @return array|mixed
     * @throws \yii\base\Exception
     */
    public function getPages()
    {
        $userId = $this->user->id;
        $likes = $this->getUserLikes();

        return [
            'user' => [
                'account' => ArrayHelper::toArray($this->user, [
                    User::class => [
                        'id', 'username', 'registration_ip',
                        'email', 'unconfirmed_email',
                        'blocked_at', 'flags',
                        'confirmed_at', 'created_at', 'updated_at', 'last_login_at',
                    ]
                ]),
                'profile' => ArrayHelper::toArray($this->getProfile(), [
                    Profile::class => [
                        'photo_id', 'name', 'description', 'dob',
                        'sex', 'status',
                        'looking_for_sex', 'looking_for_from_age', 'looking_for_to_age',
                        'country', 'city',
                        'latitude', 'longitude',
                        'is_verified',
                        'language_id', 'timezone',
                    ]
                ]),
            ],
            'photos' => $this->getUserPhotos(),
            'balance' => [
                'balance' => Yii::$app->balanceManager->getUserBalance($userId),
                'transactions' => ArrayHelper::toArray(BalanceTransaction::find()
                    ->where(['balance_transaction.user_id' => $userId])
                    ->orderBy('id desc')
                    ->all(), [
                    BalanceTransaction::class => [
                        'id', 'amount',
                        'data' => function (BalanceTransaction $transaction) {
                            return isset($transaction->data) ? @json_decode($transaction->data) : [];
                        },
                    ]
                ]),
            ],
            'messages' => ArrayHelper::toArray($this->getMessages(), [
                Message::class => [
                    'id',
                    'text',
                    'sender' => function (Message $message) {
                        return [
                            'id' => $message->senderProfile->user_id,
                            'name' => $message->senderProfile->getDisplayName(),
                            'url' => Url::to(['/profile/view', 'username' => $message->sender->username], true),
                        ];
                    },
                    'receiver' => function (Message $message) {
                        return [
                            'id' => $message->receiverProfile->user_id,
                            'name' => $message->receiverProfile->getDisplayName(),
                            'url' => Url::to(['/profile/view', 'username' => $message->receiver->username], true),
                        ];
                    },
                    'is_new',
                    'created_at',
                ]
            ]),
            'likes' => [
                'from' => ArrayHelper::toArray($likes[LikeManager::TYPE_FROM_CURRENT_USER], [
                    User::class => [
                        'id',
                        'username',
                        'name' => function (User $user) {
                            return $user->profile->getDisplayName();
                        },
                        'url' => function (User $user) {
                            return Url::to(['/profile/view', 'username' => $user->username], true);
                        },
                    ],
                ]),
                'to' => ArrayHelper::toArray($likes[LikeManager::TYPE_TO_CURRENT_USER], [
                    User::class => [
                        'id',
                        'username',
                        'name' => function (User $user) {
                            return $user->profile->getDisplayName();
                        },
                        'url' => function (User $user) {
                            return Url::to(['/profile/view', 'username' => $user->username], true);
                        },
                    ],
                ]),
                'mutual' => ArrayHelper::toArray($likes[LikeManager::TYPE_MUTUAL], [
                    User::class => [
                        'id',
                        'username',
                        'name' => function (User $user) {
                            return $user->profile->getDisplayName();
                        },
                        'url' => function (User $user) {
                            return Url::to(['/profile/view', 'username' => $user->username], true);
                        },
                    ],
                ]),
            ],
            'guests' => ArrayHelper::toArray($this->getGuests(), [
                Guest::class => [
                    'user' => function (Guest $guest) {
                        return [
                            'id' => $guest->id,
                            'name' => $guest->fromUser->profile->getDisplayName(),
                            'username' => $guest->fromUser->username,
                            'url' => Url::to(['/profile/view', 'username' => $guest->fromUser->username], true),
                        ];
                    },
                    'created_at',
                ]
            ]),
        ];
    }

    /**
     * @param \app\models\Photo $photo
     * @return array|mixed
     */
    public function preparePhoto($photo)
    {
        return [
            'path' => './media/' . $photo->source,
            'thumbnailPath' => './media/thumbnails/' . $photo->source,
        ];
    }
}
