<?php

/* @var $content string */

?>
<?php $this->beginContent('@theme/views/layouts/base.php'); ?>
<div class="page">
    <?= $this->render('//partials/important-messages') ?>
    <div class="page-main">
        <?= $this->render('//partials/header') ?>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= $this->render('//partials/header-navigation') ?>
        <?php endif; ?>
        <?php echo $content ?>
    </div>
    <?= $this->render('//partials/footer') ?>
</div>
<?php if (!$this->getParam('user.hasPhoto', true)): ?>
    <?= $this->render('//partials/user-without-photo') ?>
<?php endif; ?>
<?php $this->endContent(); ?>
