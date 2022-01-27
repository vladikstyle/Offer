<?php

/* @var $content string */

$pageWrapperClass = $this->params['pageWrapper.cssClass'] ?? '';
?>
<?php $this->beginContent('@theme/views/layouts/base.php'); ?>
<div class="page page-fill-wrapper <?= $pageWrapperClass ?>">
    <?= $this->render('//partials/important-messages') ?>
    <div class="page-fill d-flex flex-fill flex-column align-items-stretch">
        <?= $this->render('//partials/header') ?>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= $this->render('//partials/header-navigation') ?>
        <?php endif; ?>
        <div class="content d-flex flex-column" style="flex: 1; ">
            <div class="container d-flex flex-row" style=" flex: 1;">
                <?php echo $content ?>
            </div>
        </div>
    </div>
    <?= $this->render('//partials/footer') ?>
</div>
<?php if (!$this->getParam('user.hasPhoto', true)): ?>
    <?= $this->render('//partials/user-without-photo') ?>
<?php endif; ?>
<?php $this->endContent(); ?>
