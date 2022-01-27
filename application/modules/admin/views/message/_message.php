<?php

use app\helpers\Html;
use app\models\MessageAttachment;

/** @var $model \app\models\Message */
/** @var $this \app\base\View */

$messageAttachmentsData = Yii::$app->messageManager->getMessageAttachmentsData($model);
?>

<?php if (!empty($model->text)): ?>
<div class="message-text">
    <?= nl2br(Html::encode(trim($model->text))) ?>
</div>
<?php endif; ?>

<div class="message-attachments">
    <?php foreach ($messageAttachmentsData as $messageAttachment): ?>
        <div class="message-attachment">
            <?php if ($messageAttachment['type'] == MessageAttachment::TYPE_IMAGE): ?>
                <?= Html::a(Html::img($messageAttachment['thumbnail']), $messageAttachment['url']) ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
