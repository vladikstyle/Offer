<?php

use app\helpers\Html;

/** @var $this \yii\web\View */
/** @var $user \app\models\User */
/** @var $isIpBanned boolean */

$this->title = Yii::t('app', 'User info');
?>

<?php $this->beginContent('@app/modules/admin/views/user/update.php', ['user' => $user]) ?>

<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= Yii::t('app', 'Account info') ?></h2>
    </div>
    <div class="box-body no-padding">
        <table class="table">
            <tr>
                <td style="width: 25%"><strong><?= Yii::t('app', 'User ID') ?>:</strong></td>
                <td><?= $user->id ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Registration time') ?>:</strong></td>
                <td><?= Yii::t('app', '{0, date, MMMM dd, YYYY HH:mm}', [$user->created_at]) ?></td>
            </tr>
            <?php if ($user->registration_ip !== null): ?>
                <tr>
                    <td><strong><?= Yii::t('app', 'Registration IP') ?>:</strong></td>
                    <td>
                        <?php if ($isIpBanned): ?>
                            <span class="label label-danger"><?= $user->registration_ip ?></span>
                        <?php else: ?>
                            <span class="label label-default"><?= $user->registration_ip ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif ?>
            <tr>
                <td><strong><?= Yii::t('app', 'Confirmation status') ?>:</strong></td>
                <?php if ($user->isConfirmed): ?>
                    <td class="text-success">
                        <?= Yii::t('app', 'Confirmed at {0, date, MMMM dd, YYYY HH:mm}', [$user->confirmed_at]) ?>
                    </td>
                <?php else: ?>
                    <td class="text-danger"><?= Yii::t('app', 'Unconfirmed') ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Block status') ?>:</strong></td>
                <?php if ($user->isBlocked): ?>
                    <td class="text-danger">
                        <?= Yii::t('app', 'Blocked at {0, date, MMMM dd, YYYY HH:mm}', [$user->blocked_at]) ?>
                    </td>
                <?php else: ?>
                    <td class="text-success"><?= Yii::t('app', 'Not blocked') ?></td>
                <?php endif ?>
            </tr>
        </table>

    </div>
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h2 class="box-title"><?= Yii::t('app', 'Profile info') ?></h2>
    </div>
    <div class="box-body no-padding">
        <table class="table">
            <tr>
                <td style="width: 25%"><strong><?= Yii::t('app', 'Name') ?>:</strong></td>
                <td><?= Html::encode($user->profile->name) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Description') ?>:</strong></td>
                <td><?= Html::encode($user->profile->description) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Sex') ?>:</strong></td>
                <td><?= Html::encode($user->profile->getSexTitle()) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Status') ?>:</strong></td>
                <td><?= Html::encode($user->profile->getStatusTitle()) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Location') ?>:</strong></td>
                <td><?= Html::encode($user->profile->getDisplayLocation()) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Birthdate') ?>:</strong></td>
                <td><?= Html::encode($user->profile->dob) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('app', 'Premium account') ?>:</strong></td>
                <td>
                    <?php if ($user->isPremium): ?>
                        <?= Html::tag('span', Yii::t('app', 'Yes'), ['class' => 'text-success']) ?>
                    <?php else: ?>
                        <?= Html::tag('span', Yii::t('app', 'No'), ['class' => 'text-warning']) ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php $this->endContent() ?>
