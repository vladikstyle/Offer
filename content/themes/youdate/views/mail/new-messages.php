<?php

use app\helpers\Html;
use app\helpers\Url;

/** @var \app\models\Message[] $messages */
/** @var \app\models\User $user */
/** @var \app\base\View $this */

$iconUrl = Url::to(['@themeUrl/static/images/new-messages.png'], true);
?>

<div style="height: 1px; width: 100%; background: #eee; margin-top: 15px; margin-bottom: 15px;"></div>

<p style="text-align:center; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; font-weight: normal; margin: 15px 0 15px; padding: 0; color: #777;">
    <?= Yii::t('youdate', 'You have new messages') ?>:
</p>

<table class="box" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; border-radius: 3px;">
    <?php foreach ($messages as $message): ?>
    <?php $messageAttachmentsData = Yii::$app->messageManager->getMessageAttachmentsData($message); ?>
    <tr>
        <td class="w-1p" style="padding-top: 8px; padding-bottom: 8px; width: 1%;">
            <a href="<?= Url::to(['/profile/view', 'username' => $message->sender->username], true) ?>">
                <img src="<?= $message->sender->profile->getAvatarUrl(80, 80) ?>"
                     class="avatar"
                     width="40"
                     height="40" alt="" style="line-height: 100%; border: 0 none; outline: none; text-decoration: none; vertical-align: baseline; font-size: 0; border-radius: 50%; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);">
            </a>
        </td>
        <td class="pl-md" style="font-family: Open Sans, -apple-system, BlinkMacSystemFont, Roboto, Helvetica Neue, Helvetica, Arial, sans-serif; padding-top: 8px; padding-bottom: 8px; padding-left: 16px;">
            <strong style="color: #556; font-size: 14px;"><?= Html::encode($message->sender->profile->getDisplayName()) ?></strong>
            <br>
            <?= Html::encode($message->text) ?>
            <?php foreach ($messageAttachmentsData as $messageAttachment): ?>
                <img src="<?= $messageAttachment['thumbnail'] ?>" alt="<?= Html::encode($message->sender->username) ?>"
                     style="border-radius: 4px; max-width: 100%; height: auto; margin-top: 6px; margin-bottom: 6px;">
            <?php endforeach; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p style="font-size: 14px; line-height: 1.6; margin: 15px 0 10px; background: #2b7ae4; color: #fff; padding: 6px 10px; border-radius: 4px; text-align: center; font-weight: bolder;">
    <a href="<?= Url::to(['/messages/index'], true) ?>" style="color: #fff; text-decoration: none">
        <?= Yii::t('youdate', 'View messages') ?>
    </a>
</p>
