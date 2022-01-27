<?php

/** @var \app\models\Message[] $messages */
/** @var \app\models\User $user */
/** @var \app\base\View $this */

?>
<?= Yii::t('youdate', 'Hello') ?>,

<?= Yii::t('youdate', 'You have new messages') ?>:

<?php foreach ($messages as $message): ?>
<?php printf("%s: %s at %s\n", $message->sender->profile->getDisplayName(), $message->text, Yii::$app->formatter->asDatetime($message->created_at)); ?>
<?php endforeach; ?>
