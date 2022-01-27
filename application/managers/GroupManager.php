<?php

namespace app\managers;

use app\base\ActiveDataProvider;
use app\events\FormEvent;
use app\files\Storage;
use app\forms\PostForm;
use app\models\Group;
use app\models\GroupPost;
use app\models\GroupUser;
use app\models\Post;
use app\models\PostAttachment;
use app\models\query\GroupQuery;
use app\models\query\GroupUserQuery;
use app\models\Upload;
use app\models\User;
use app\modules\admin\components\Permission;
use app\traits\EventTrait;
use app\traits\SettingsTrait;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\managers
 */
class GroupManager extends Component
{
    use EventTrait, SettingsTrait;

    const EVENT_BEFORE_CREATE_GROUP = 'beforeCreateGroup';
    const EVENT_AFTER_CREATE_GROUP = 'afterCreateGroup';
    const EVENT_BEFORE_JOIN_GROUP = 'beforeJoinGroup';
    const EVENT_AFTER_JOIN_GROUP = 'afterJoinGroup';
    const EVENT_BEFORE_LEAVE_GROUP = 'beforeLeaveGroup';
    const EVENT_AFTER_LEAVE_GROUP = 'afterLeaveGroup';
    const EVENT_BEFORE_APPROVE_MEMBER = 'beforeApproveMember';
    const EVENT_AFTER_APPROVE_MEMBER = 'afterApproveMember';
    const EVENT_BEFORE_DECLINE_MEMBER = 'beforeDeclineMember';
    const EVENT_AFTER_DECLINE_MEMBER = 'afterDeclineMember';
    const EVENT_BEFORE_TOGGLE_ADMIN = 'beforeToggleAdmin';
    const EVENT_AFTER_TOGGLE_ADMIN = 'afterToggleAdmin';
    const EVENT_BEFORE_TOGGLE_BAN = 'beforeToggleBan';
    const EVENT_AFTER_TOGGLE_BAN = 'afterToggleBan';
    const EVENT_BEFORE_CREATE_POST = 'beforeCreatePost';
    const EVENT_AFTER_CREATE_POST = 'afterCreatePost';

    /**
     * @var Storage
     */
    public $photoStorage;

    public function init()
    {
        parent::init();
        if (!isset($this->photoStorage)) {
            $this->photoStorage = Yii::$app->photoStorage;
        }
    }

    /**
     * @param $id
     * @return Group|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function getGroup($id)
    {
        return $this->getQuery()->andWhere(['group.id' => $id])->one();
    }

    /**
     * @param $alias
     * @return Group|array|null|\yii\db\ActiveRecord
     * @throws \Exception
     */
    public function getGroupByAlias($alias)
    {
        $query = $this->getQuery()
            ->byAlias(['alias' => $alias]);

        return $query->one();
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function getGroupsProvider($params = [])
    {
        $query = $this->getQuery()
            ->joinWith(['groupUsers'])
            ->groupBy('group.id')
            ->notBlocked()
            ->orderBy('group.members_count desc');

        $visibility = ArrayHelper::getValue($params, 'visibility');
        if ($visibility) {
            $query->andWhere(['group.visibility' => $visibility]);
        }

        $searchQuery = ArrayHelper::getValue($params, 'searchQuery');
        if ($searchQuery) {
            $query->andFilterWhere(['or',
                ['like', 'title', $searchQuery],
                ['like', 'description', $searchQuery]
            ]);
        }

        /** @var User|null $forUser */
        $forUser = ArrayHelper::getValue($params, 'forUser');
        if ($forUser instanceof User) {
            $query->andFilterWhere(['or',
                ['group.user_id' => $forUser->id],
                ['group_user.user_id' => $forUser->id],
            ]);
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * @param User $user
     * @param Group $group
     * @return GroupUser|array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupUser(User $user, Group $group)
    {
        return $this->getMemberQuery()->andWhere(['group_id' => $group->id, 'user_id' => $user->id])->one();
    }

    /**
     * @param User|null $user
     * @param Group $group
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function canView($user, Group $group)
    {
        $groupVisible = $group->visibility === Group::VISIBILITY_VISIBLE;
        if ($user === null) {
            return $groupVisible;
        }

        if ($user->isAdmin || ($user->isModerator && $user->hasPermission(Permission::GROUPS))) {
            return true;
        }

        $groupUser = $this->getGroupUser($user, $group);

        if ($groupUser === null) {
            return $groupVisible;
        } else {
            return $groupUser->status === GroupUser::STATUS_MEMBER;
        }
    }

    /**
     * @param User $user
     * @param Group $group
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function canManage(User $user, Group $group)
    {
        if ($user->isAdmin || ($user->isModerator && $user->hasPermission(Permission::GROUPS))) {
            return true;
        }

        if ($group->user_id === $user->id) {
            return true;
        }

        $groupUser = $this->getGroupUser($user, $group);
        if ($group->user_id == $user->id || ($groupUser && $groupUser->role == GroupUser::ROLE_ADMIN)) {
            return true;
        }

        return false;
    }

    /**
     * @return GroupQuery|object
     * @throws \yii\base\InvalidConfigException
     */
    public function getQuery()
    {
        return Yii::createObject(GroupQuery::class, [Group::class]);
    }

    /**
     * @return GroupUserQuery|object
     * @throws \yii\base\InvalidConfigException
     */
    public function getMemberQuery()
    {
        return Yii::createObject(GroupUserQuery::class, [GroupUser::class]);
    }

    /**
     * @param Group $group
     * @param $groupUserId
     * @return GroupUser|array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupMemberById(Group $group, $groupUserId)
    {
        return $this->getMemberQuery()->andWhere(['group_user.group_id' => $group->id, 'group_user.id' => $groupUserId])->one();
    }

    /**
     * @param Group $group
     * @param User $user
     * @return GroupUser|array|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupMemberByUser(Group $group, User $user)
    {
        return $this->getMemberQuery()->andWhere(['group_user.group_id' => $group->id, 'group_user.user_id' => $user->id])->one();
    }

    /**
     * @param Group $group
     * @param User $user
     * @return Group
     * @throws \yii\base\InvalidConfigException
     */
    public function createGroup(Group $group, User $user)
    {
        $event = $this->getGroupEvent($group);
        $this->trigger(self::EVENT_BEFORE_CREATE_GROUP, $event);
        if (!$event->isValid) {
            return null;
        }

        $group->save();
        $this->joinGroup($group, $user);
        $this->trigger(self::EVENT_AFTER_CREATE_GROUP, $event);

        return $group;
    }

    /**
     * @param Group $group
     * @param User $user
     * @param string $role
     * @return GroupUser|null
     * @throws \yii\base\InvalidConfigException
     */
    public function joinGroup(Group $group, User $user, $role = GroupUser::ROLE_MEMBER)
    {
        $event = $this->getGroupEvent($group);
        $event->user = $user;

        $this->trigger(self::EVENT_BEFORE_JOIN_GROUP, $event);
        if (!$event->isValid) {
            return null;
        }

        /** @var GroupUser $groupUser */
        $groupUser = Yii::createObject(GroupUser::class);
        $groupUser->group_id = $group->id;
        $groupUser->user_id = $user->id;

        if ($group->user_id == $user->id) {
            $groupUser->status = GroupUser::STATUS_MEMBER;
            $groupUser->role = GroupUser::ROLE_ADMIN;
        } else {
            $groupUser->status = $group->visibility == Group::VISIBILITY_PRIVATE ? GroupUser::STATUS_UNDER_MODERATION : GroupUser::STATUS_MEMBER;
            $groupUser->role = $role;
        }

        $groupUser->save();
        $group->updateMembersCount();

        $this->trigger(self::EVENT_AFTER_JOIN_GROUP, $event);

        return $groupUser;
    }

    /**
     * @param Group $group
     * @param User $user
     * @return bool|false|int
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function leaveGroup(Group $group, User $user)
    {
        $event = $this->getGroupEvent($group);
        $event->user = $user;

        $this->trigger(self::EVENT_BEFORE_LEAVE_GROUP, $event);
        if (!$event->isValid) {
            return false;
        }

        $groupUser = $this->getMemberQuery()->where(['group_id' => $group->id, 'user_id' => $user->id])->one();
        if ($groupUser === null) {
            return false;
        }

        $this->trigger(self::EVENT_AFTER_LEAVE_GROUP, $event);

        $status = $groupUser->delete();
        $group->updateMembersCount();

        return $status;
    }

    /**
     * @param GroupUser $groupUser
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function approveMember(GroupUser $groupUser)
    {
        return $this->groupMemberAction($groupUser, self::EVENT_BEFORE_APPROVE_MEMBER, self::EVENT_AFTER_APPROVE_MEMBER,
            function (GroupUser $groupUser) {
                return $groupUser->approve();
            }
        );
    }

    /**
     * @param GroupUser $groupUser
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function declineMember(GroupUser $groupUser)
    {
        return $this->groupMemberAction($groupUser, self::EVENT_BEFORE_DECLINE_MEMBER, self::EVENT_AFTER_DECLINE_MEMBER,
            function (GroupUser $groupUser) {
                return $groupUser->delete();
            }
        );
    }

    /**
     * @param GroupUser $groupUser
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function toggleBan(GroupUser $groupUser)
    {
        return $this->groupMemberAction($groupUser, self::EVENT_BEFORE_TOGGLE_BAN, self::EVENT_AFTER_TOGGLE_BAN,
            function (GroupUser $groupUser) {
                return $groupUser->toggleBan();
            }
        );
    }

    /**
     * @param GroupUser $groupUser
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function toggleAdmin(GroupUser $groupUser)
    {
        return $this->groupMemberAction($groupUser, self::EVENT_BEFORE_TOGGLE_ADMIN, self::EVENT_AFTER_TOGGLE_ADMIN,
            function (GroupUser $groupUser) {
                return $groupUser->toggleAdmin();
            }
        );
    }

    /**
     * @param GroupUser $groupUser
     * @param $eventBefore
     * @param $eventAfter
     * @param $action
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    protected function groupMemberAction(GroupUser $groupUser, $eventBefore, $eventAfter, $action)
    {
        $event = $this->getGroupEvent($groupUser->group);
        $event->groupUser = $groupUser;

        $this->trigger($eventBefore, $event);
        if (!$event->isValid) {
            return false;
        }

        $status = false;
        if (is_callable($action)) {
            $status = call_user_func($action, $groupUser);
        }

        $this->trigger($eventAfter, $event);

        return $status;
    }

    /**
     * @param Group $group
     * @param array $params
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function getMembersProvider(Group $group, $params = [])
    {
        $query = $this->getMemberQuery()
            ->joinWith(['user', 'user.profile'])
            ->whereGroup($group);

        $withoutBanned = ArrayHelper::getValue($params, 'withoutBanned');
        if ($withoutBanned) {
            $query->withoutBanned();
        }

        $status = ArrayHelper::getValue($params, 'status');
        if ($status) {
            $query->whereStatus($status);
        }

        $searchQuery = ArrayHelper::getValue($params, 'searchQuery');
        if ($searchQuery) {
            $query->andFilterWhere(['or',
                ['like', 'user.username', $searchQuery],
                ['like', 'profile.name', $searchQuery]
            ]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => 'created_at desc']
        ]);
    }

    /**
     * @param Group $group
     * @return ActiveDataProvider
     */
    public function getPostsDataProvider(Group $group)
    {
        $query = Post::find()->joinWith(['user', 'user.profile', 'attachments'])
            ->leftJoin('group_post', 'group_post.post_id = post.id')
            ->withVoteAggregate('postLike')
            ->withUserVote('postLike')
            ->andWhere(['group_post.group_id' => $group->id])
            ->orderBy('post.id desc');

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => 'id desc'],
            'totalCountCallback' => function (ActiveDataProvider $activeDataProvider) use ($group) {
                return GroupPost::find()
                    ->where(['group_post.group_id' => $group->id])
                    ->count();
            },
        ]);
    }

    /**
     * @param Group $group
     * @param PostForm $postForm
     * @return GroupPost|null
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function createPost(Group $group, PostForm $postForm)
    {
        $event = new FormEvent();
        $event->setForm($postForm);
        $this->trigger(self::EVENT_BEFORE_CREATE_POST, $event);

        if (!$event->isValid) {
            return null;
        }

        /** @var Post $post */
        $post = Yii::createObject(Post::class);
        $post->content = $postForm->content;
        $post->user_id = $postForm->user->id;
        if (!$post->save()) {
            throw new Exception('Could not create post record');
        }

        $groupPost = new GroupPost();
        $groupPost->group_id = $group->id;
        $groupPost->post_id = $post->id;
        if (!$groupPost->save()) {
            throw new Exception('Could not create group post record');
        }

        $event->extraData = $groupPost;
        $this->trigger(self::EVENT_AFTER_CREATE_POST, $event);

        if (is_array($postForm->attachments) && count($postForm->attachments)) {
            foreach ($postForm->attachments as $attachment) {
                if (!isset($attachment['path'])) {
                    break;
                }
                $uploadModel = Upload::findOne(['user_id' => Yii::$app->user->id, 'path' => $attachment['path']]);
                if ($uploadModel !== null && $this->photoStorage->isFileExists($attachment['path'])) {
                    $attachmentModel = new PostAttachment();
                    $attachmentModel->post_id = $post->id;
                    $attachmentModel->type = PostAttachment::TYPE_IMAGE;
                    $attachmentModel->data = $attachment['path'];
                    if ($attachmentModel->save()) {
                        $attachmentsIds[] = $attachmentModel->id;
                    } else {
                        Yii::warning($attachmentModel->errors);
                    }
                    $uploadModel->delete();
                } else {
                    Yii::error('Upload file error');
                }
            }
        }

        return $groupPost;
    }

    /**
     * @param GroupPost $groupPost
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deletePost($groupPost)
    {
        $postAttachments = $groupPost->post->attachments;
        if (count($postAttachments)) {
            foreach ($postAttachments as $postAttachment) {
                if ($postAttachment->type == PostAttachment::TYPE_IMAGE) {
                    $this->photoStorage->delete($postAttachment->data);
                }
            }
        }
        return $groupPost->post->delete();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isGroupsFeatureEnabled()
    {
        return (bool) $this->settings->get('frontend', 'siteGroupsEnabled');
    }
}
