<?php

use app\helpers\Html;

/** @var $content string */
/** @var $title string */
/** @var $messages \app\models\Message[] */

?>
<?php $this->beginContent('@theme/views/data-export/layout.php'); ?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <?= Html::encode($title) ?>
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table table-outline table-vcenter card-table">
            <thead>
            <tr>
                <th colspan="2"><?= Yii::t('youdate', 'Sender') ?></th>
                <th colspan="2"><?= Yii::t('youdate', 'Receiver') ?></th>
                <th><?= Yii::t('youdate', 'Message') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $message): ?>
                <tr data-key="8">
                    <td><span class="avatar" style="background-image: url('<?= $message->senderProfile->getAvatarUrl() ?>')"></span></td>
                    <td><?= Html::encode($message->senderProfile->getDisplayName()) ?></td>
                    <td><span class="avatar" style="background-image: url('<?= $message->receiverProfile->getAvatarUrl() ?>')"></span></td>
                    <td><?= Html::encode($message->receiverProfile->getDisplayName()) ?></td>
                    <td class="expand">
                        <div class="date text-muted"><?= $message->getCreatedDateTime() ?></div>
                        <div class="message"><?= Html::encode($message->text) ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->endContent(); ?>
