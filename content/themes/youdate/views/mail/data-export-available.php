<?php

use yii\helpers\Html;

/** @var \app\models\User $user */
/** @var \app\base\View $this */
/** @var string $downloadUrl */
?>
<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('youdate', 'Your profile data archive is available for download') ?>:
</p>
<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Html::a(Yii::t('youdate', 'Download archive'), $downloadUrl); ?>
</p>
