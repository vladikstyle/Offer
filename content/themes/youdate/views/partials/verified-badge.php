<?php

/** @var $model \app\models\User */

use youdate\helpers\Icon;

?>
<div class="user-verified-badge d-flex align-items-center justify-content-center" rel="tooltip"
     title="<?= Yii::t('youdate', 'Verified user') ?>">
    <?= Icon::fe('check') ?>
</div>
