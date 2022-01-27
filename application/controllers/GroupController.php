<?php

namespace app\controllers;

use app\actions\GlideAction;
use app\base\Controller;
use app\forms\PostForm;
use app\helpers\Url;
use app\models\Group;
use app\models\GroupPost;
use app\models\GroupUser;
use app\models\Post;
use app\traits\AjaxValidationTrait;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class GroupController extends Controller
{
    use AjaxValidationTrait;

    const PAGE_FEED = 'feed';
    const PAGE_MEMBERS = 'members';
    const PAGE_INFO = 'info';

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        return [
            'thumbnail' => [
                'class' => GlideAction::class,
                'imageFile' => function() {
                    $groupId = $this->request->get('id');
                    $group = $this->groupManager->getGroup($groupId);
                    if ($group == null) {
                        throw new NotFoundHttpException('Group not found');
                    }

                    return $group->photo_path;
                },
            ],
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function behaviors()
    {
        $hideGroups = Yii::$app->settings->get('frontend', 'siteHideGroupsFromGuests', false) &&
            Yii::$app->user->isGuest;

        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                    ['allow' => true, 'actions' => ['view'], 'roles' => [$hideGroups ? '@' : '?']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'join' => ['post'],
                    'leave' => ['post'],
                    'approve' => ['post'],
                    'decline' => ['post'],
                    'toggle-ban' => ['post'],
                    'toggle-admin' => ['post'],
                    'new-post' => ['post'],
                ],
            ],
            'cache' => [
                'class' => 'yii\filters\HttpCache',
                'only' => ['thumbnail'],
                'lastModified' => function ($action, $params) {
                    $group = $this->groupManager->getGroup($this->request->get('id'));
                    if ($group !== null && isset($group->updated_at)) {
                        return $group->updated_at;
                    }
                    return null;
                },
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!$this->groupManager->isGroupsFeatureEnabled()) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

    /**
     * @param bool $forCurrentUser
     * @return string
     * @throws \Exception
     */
    public function actionIndex($forCurrentUser = false)
    {
        if (Yii::$app->settings->get('frontend', 'siteHideGroupsFromGuests', false) == true && Yii::$app->user->isGuest) {
            return $this->redirect(['/security/login']);
        }

        $user = $this->getCurrentUser();
        $searchQuery = $this->request->get('q');
        $dataProviderParams = ['searchQuery' => $searchQuery];
        if ($forCurrentUser == true) {
            $dataProviderParams['forUser'] = $user;
        }

        return $this->render('index', [
            'dataProvider' => $this->groupManager->getGroupsProvider($dataProviderParams),
            'user' => $this->getCurrentUser(),
            'searchQuery' => $searchQuery,
            'forCurrentUser' => $forCurrentUser,
        ]);
    }

    /**
     * @param $alias
     * @param string $subPage
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($alias, $subPage = 'feed')
    {
        if (!in_array($subPage, [self::PAGE_FEED, self::PAGE_INFO, self::PAGE_MEMBERS])) {
            throw new NotFoundHttpException();
        }

        if (Yii::$app->settings->get('frontend', 'siteHideGroupsFromGuests', false) == true && Yii::$app->user->isGuest) {
            return $this->redirect(['/security/login']);
        }

        $postForm = Yii::createObject(Post::class);
        $user = $this->getCurrentUser();
        $group = $this->findGroup($alias);
        $groupUser = null;

        if ($user !== null) {
            $groupUser = $this->groupManager->getGroupUser($user, $group);
            $canView = $this->groupManager->canView($user, $group);
            $canManage = $this->groupManager->canManage($user, $group);
        } else {
            $canView = $group->visibility == Group::VISIBILITY_VISIBLE;
            $canManage = false;
        }

        return $this->render($subPage, [
            'user' => $user,
            'group' => $group,
            'groupUser' => $groupUser,
            'canView' => $canView,
            'canManage' => $canManage,
            'subPage' => $subPage,
            'postForm' => $postForm,
        ]);
    }

    /**
     * @param $alias
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionJoin($alias)
    {
        $group = $this->findGroup($alias);
        $user = $this->getCurrentUser();

        $this->groupManager->joinGroup($group, $user);

        return $this->redirect(['view', 'alias' => $group->alias]);
    }

    /**
     * @param $alias
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionLeave($alias)
    {
        $group = $this->findGroup($alias);
        $user = $this->getCurrentUser();

        $this->groupManager->leaveGroup($group, $user);

        return $this->redirect(['view', 'alias' => $group->alias]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        /** @var Group $group */
        $group = Yii::createObject(Group::class);
        $group->scenario = Group::SCENARIO_CREATE;
        $group->user_id = $this->getCurrentUser()->id;

        $this->performAjaxValidation($group);

        if ($group->load($this->request->post()) && $group->validate()) {
            $group = $this->groupManager->createGroup($group, $this->getCurrentUser());
            return $this->redirect(['view', 'alias' => $group->alias]);
        }

        return $this->render('create', [
            'group' => $group,
        ]);
    }

    /**
     * @param $alias
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\ExitException
     */
    public function actionManagementUpdate($alias)
    {
        $user = $this->getCurrentUser();
        $group = $this->findGroup($alias, true);
        $groupUser = $this->groupManager->getGroupUser($user, $group);

        $group->scenario = Group::SCENARIO_UPDATE;
        $this->performAjaxValidation($group);

        if ($group->load($this->request->post()) && $group->save()) {
            $this->session->setFlash('success', Yii::t('app', 'Group info has been updated'));
            return $this->refresh();
        }

        return $this->render('management-update', [
            'user' => $user,
            'group' => $group,
            'groupUser' => $groupUser,
        ]);
    }

    /**
     * @param $alias
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionManagementUsers($alias)
    {
        $user = $this->getCurrentUser();
        $group = $this->findGroup($alias, true);
        $groupUser = $this->groupManager->getGroupUser($user, $group);
        $searchQuery = $this->request->get('q');
        $status = $this->request->get('status');
        $dataProvider = $this->groupManager->getMembersProvider($group, [
            'searchQuery' => $searchQuery,
            'status' => $status
        ]);

        Url::remember($this->request->url, 'groups-users-management');

        return $this->render('management-users', [
            'user' => $user,
            'group' => $group,
            'groupUser' => $groupUser,
            'dataProvider' => $dataProvider,
            'searchQuery' => $searchQuery,
            'status' => $status,
        ]);
    }

    /**
     * @param $alias
     * @param $groupUserId
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionApprove($alias, $groupUserId)
    {
        return $this->groupUserAction($alias, $groupUserId, function (GroupUser $groupUser) {
            $this->groupManager->approveMember($groupUser);
            return Yii::t('app', 'User has been approved');
        });
    }

    /**
     * @param $alias
     * @param $groupUserId
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDecline($alias, $groupUserId)
    {
        return $this->groupUserAction($alias, $groupUserId, function (GroupUser $groupUser) {
            $this->groupManager->declineMember($groupUser);
            return Yii::t('app', 'User has been declined');
        });
    }

    /**
     * @param $alias
     * @param $groupUserId
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionToggleBan($alias, $groupUserId)
    {
        return $this->groupUserAction($alias, $groupUserId, function (GroupUser $groupUser) {
            $groupUser->toggleBan();
            return $groupUser->status === GroupUser::STATUS_BANNED ?
                Yii::t('app', 'User has been banned') :
                Yii::t('app', 'User has been unbanned');
        });
    }

    /**
     * @param $alias
     * @param $groupUserId
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionToggleAdmin($alias, $groupUserId)
    {
        return $this->groupUserAction($alias, $groupUserId, function (GroupUser $groupUser) {
            $groupUser->toggleAdmin();
            return $groupUser->role === GroupUser::ROLE_ADMIN ?
                Yii::t('app', 'User has been added to group admins') :
                Yii::t('app', 'User has been removed from group admins');
        });
    }

    /**
     * @param $alias
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionNewPost($alias)
    {
        $user = $this->getCurrentUser();
        $group = $this->findGroup($alias);

        if (!$this->groupManager->canView($user, $group)) {
            throw new ForbiddenHttpException();
        }


        /** @var PostForm $postForm */
        $postForm = Yii::createObject(PostForm::class);
        $postForm->user = $this->getCurrentUser();
        $highlightPostId = null;
        $groupPost = null;

        $this->performAjaxValidation($postForm);

        if ($postForm->load($this->request->post()) && $postForm->validate()) {
            $groupPost = $this->groupManager->createPost($group, $postForm);
            $highlightPostId = $groupPost->post_id;
        }

        return $this->redirect(['view', 'alias' => $group->alias, 'highlightPostId' => $highlightPostId]);
    }

    /**
     * @param $alias
     * @param $postId
     * @return bool
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeletePost($alias, $postId)
    {
        $user = $this->getCurrentUser();
        $group = $this->findGroup($alias);
        $groupPost = $this->findPost($group, $postId);
        $canDelete = $this->groupManager->canManage($user, $group) || $groupPost->post->user_id == $user->id;

        if ($canDelete) {
            $this->groupManager->deletePost($groupPost);
            return $this->sendJson(['success' => true, 'message' => Yii::t('app', 'Post has been deleted')]);
        }

        return $this->sendJson(['success' => false, 'message' => Yii::t('app', 'You can not delete this post')]);
    }

    public function actionReportPost($alias)
    {

    }

    /**
     * @param $alias
     * @param $groupUserId
     * @param callable $action
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function groupUserAction($alias, $groupUserId, $action)
    {
        $group = $this->findGroup($alias, true);
        $groupUser = $this->findGroupMember($group, $groupUserId);

        if (is_callable($action)) {
            $message = call_user_func($action, $groupUser);
            $this->session->setFlash('success', $message);
        }

        return $this->redirect(Url::previous('groups-users-management'));
    }

    /**
     * @param $alias
     * @param bool $checkAdminRights
     * @return Group|array|null|\yii\db\ActiveRecord
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function findGroup($alias, $checkAdminRights = false)
    {
        $group = $this->groupManager->getGroupByAlias($alias);

        if ($group == null) {
            throw new NotFoundHttpException(Yii::t('app', 'Group not found'));
        }

        if (!$checkAdminRights && $group->visibility === Group::VISIBILITY_BLOCKED) {
            throw new NotFoundHttpException(Yii::t('app', 'Group has been blocked'));
        }

        if ($checkAdminRights && !$this->groupManager->canManage($this->getCurrentUser(), $group)) {
            throw new ForbiddenHttpException(Yii::t('app', 'You don\'t have admin access to this group'));
        }

        return $group;
    }

    /**
     * @param Group $group
     * @param $groupUserId
     * @return \app\models\GroupUser|array|null
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function findGroupMember(Group $group, $groupUserId)
    {
        $groupUser = $this->groupManager->getGroupMemberById($group, $groupUserId);
        if ($groupUser === null) {
            throw new NotFoundHttpException(Yii::t('app', 'Group member not found'));
        }

        return $groupUser;
    }

    /**
     * @param Group $group
     * @param $postId
     * @return GroupPost|array|null
     * @throws NotFoundHttpException
     */
    protected function findPost(Group $group, $postId)
    {
        $groupPost = GroupPost::find()
            ->where(['group_post.group_id' => $group->id, 'group_post.post_id' => $postId])
            ->joinWith(['post'])
            ->one();

        if ($groupPost === null) {
            throw new NotFoundHttpException(Yii::t('app', 'Post not found'));
        }

        return $groupPost;
    }
}
