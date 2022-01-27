<?php

namespace app\controllers;

use app\actions\GlideAction;
use app\forms\MessageAttachmentForm;
use app\forms\MessageForm;
use app\helpers\Url;
use app\models\MessageAttachment;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * @author Alexander Kononenko <contact@hauntd.me>
 * @package app\controllers
 */
class MessagesController extends \app\base\Controller
{
    /**
     * @var bool
     */
    public $prepareData = false;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                    'delete-conversation' => ['post'],
                    'read-conversation' => ['post'],
                    'upload-images' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if ($action->id == 'index') {
            $this->prepareData = true;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actions()
    {
        return [
            'image-thumbnail' => [
                'class' => GlideAction::class,
                'imageFile' => function() {
                    $user = $this->getCurrentUser();
                    $messageAttachmentId = $this->request->get('id');
                    $messageAttachment = $this->messageManager->getMessageAttachment($messageAttachmentId, $user->id);

                    if ($messageAttachment == null) {
                        throw new NotFoundHttpException('Message attachment not found');
                    }

                    if ($messageAttachment->type !== MessageAttachment::TYPE_IMAGE) {
                        throw new NotFoundHttpException('Invalid message attachment type');
                    }

                    if (!$messageAttachment->message->hasAccess($user->id)) {
                        throw new ForbiddenHttpException('You don\'t have access to this attachment');
                    }

                    return $messageAttachment->data;
                },
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'user' => $this->getCurrentUser(),
            'profile' => $this->getCurrentUserProfile(),
        ]);
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionConversations()
    {
        $user = $this->getCurrentUser();
        $query = $this->request->get('query');
        $conversations = $this->messageManager->getConversations($user->id, $query);

        return $this->sendJson([
            'success' => true,
            'conversations' => $conversations,
            'newMessagesCounters' => $this->messageManager->getNewMessagesCounters($user->id),
        ]);
    }

    /**
     * @param $contactId
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionMessages($contactId)
    {
        $contact = $this->userManager->getUserById($this->request->get('contactId'));
        if ($contact == null) {
            throw new NotFoundHttpException('Contact not found');
        }
        $messages = $this->messageManager->getMessages($contactId, $this->getCurrentUser()->id);

        return $this->sendJson([
            'success' => true,
            'contact' => [
                'id' => $contact->id,
                'username' => $contact->username,
                'full_name' => $contact->profile->getDisplayName(),
                'avatar' => $contact->profile->getAvatarUrl(48, 48),
                'url' => Url::to(['/profile/view', 'username' => $contact->username]),
                'online' => $contact->isOnline,
                'verified' => (bool) $contact->profile->is_verified,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionCreate()
    {
        $contact = $this->getContactUser();
        $form = new MessageForm();
        $form->load($this->request->post());

        if ($form->validate()) {
            $message = $this->messageManager->createMessage($this->getCurrentUser()->id, $contact->id, $form->message);
            if (!$message->isNewRecord) {
                $message->refresh();
                return $this->sendJson([
                    'success' => true,
                    'message' => Yii::t('app', 'Message has been sent'),
                    'messageId' => $message->id,
                    'pendingMessageId' => (int)$this->request->post('pendingMessageId'),
                ]);
            } else {
                return $this->sendJson([
                    'success' => false,
                    'message' => Yii::t('app', $message->getErrorSummary(true)[0]),
                    'pendingMessageId' => (int) $this->request->post('pendingMessageId'),
                    'errors' => $form->errors,
                ]);
            }
        }

        return $this->sendJson([
            'success' => false,
            'message' => Yii::t('app', $form->getErrorSummary(true)[0]),
            'pendingMessageId' => (int) $this->request->post('pendingMessageId'),
            'errors' => $form->errors,
        ]);
    }

    /**
     * @throws \League\Flysystem\FileExistsException
     * @throws \yii\base\Exception
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUploadImages()
    {
        $form = new MessageAttachmentForm();
        $form->load($this->request->post());
        $form->files = UploadedFile::getInstances($form, 'files');

        if (!$form->validate()) {
            return $this->sendJson([
                'success' => false,
                'message' => $form->getFirstError('files'),
                'errors' => $form->errors,
            ]);
        }

        $message = $this->messageManager->createMessage($this->getCurrentUser()->id, $form->contactId, null);
        if (count($message->errors) > 1) {
            return $this->sendJson([
                'success' => false,
                'message' => Yii::t('app', 'Could not create message'),
            ]);
        }

        /** @var UploadedFile $file */
        foreach ($form->files as $file) {
            $filePath = Yii::$app->photoStorage->save($file);
            $this->messageManager->addAttachment($message, MessageAttachment::TYPE_IMAGE, $filePath);
        }

        return $this->sendJson([
            'success' => true,
        ]);
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionDelete()
    {
        $messageIds = $this->request->post('messages');
        $count = $this->messageManager->deleteMessages($this->getCurrentUser()->id, $messageIds);

        return $this->sendJson([
            'success' => true,
            'message' => Yii::t('app', 'Selected messages has been deleted'),
            'count' => $count,
        ]);
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionDeleteConversation()
    {
        $contact = $this->getContactUser(true);
        $success = $this->messageManager->deleteConversation($this->getCurrentUser()->id, $contact->id);

        return $this->sendJson([
            'success' => $success,
            'message' => Yii::t('app', 'Selected conversation has been deleted'),
        ]);
    }

    /**
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     */
    public function actionReadConversation()
    {
        $contact = $this->getContactUser();
        $success = $this->messageManager->readConversation($this->getCurrentUser()->id, $contact->id);

        return $this->sendJson([
            'success' => $success,
            'newMessagesCount' => $this->messageManager->getNewMessagesCounters($this->getCurrentUser()->id),
            'message' => Yii::t('app', 'Updated'),
        ]);
    }

    /**
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function actionNewMessagesCounters()
    {
        return $this->sendJson($this->messageManager->getNewMessagesCounters($this->getCurrentUser()->id));
    }

    /**
     * @param bool $includeBanned
     * @return \app\models\User|array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function getContactUser($includeBanned = false)
    {
        $userId = $this->request->getQueryParam('contactId');
        if ($userId == null) {
            $userId = $this->request->getBodyParam('contactId');
        }
        $user = $this->userManager->getUserById($userId, ['includeBanned' => $includeBanned]);
        if ($user == null) {
            throw new NotFoundHttpException('Contact not found');
        }

        return $user;
    }
}
