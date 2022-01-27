<?php

use youdate\helpers\Icon;

/** @var \app\base\View $this */

$likesForPremiumOnly = $this->frontendSetting('sitePremiumIncomingLikes', false);
?>
<div class="premium-about mt-6">
    <h4 class="mb-4"><?= Yii::t('youdate', 'Why premium?') ?></h4>
    <div class="row mb-sm-0 mb-md-3">
        <div class="col-12 col-md-6">
            <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-4 mb-md-2">
                <div class="premium-icon premium-icon-search d-flex justify-content-center align-items-center">
                    <?= Icon::fa('search') ?>
                </div>
                <div class="premium-description">
                    <div class="title"><?= Yii::t('youdate', 'Search position') ?></div>
                    <div class="description">
                        <?= Yii::t('youdate', 'Free automatic profile rise up daily') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-4 mb-md-2">
                <div class="premium-icon premium-icon-messaging d-flex justify-content-center align-items-center">
                    <?= Icon::fa('envelope') ?>
                </div>
                <div class="premium-description">
                    <div class="title"><?= Yii::t('youdate', 'Messaging priority') ?></div>
                    <div class="description">
                        <?= Yii::t('youdate', 'Have your messages read first') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-sm-0 mb-md-3">
        <div class="col-12 col-md-6">
            <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-4 mb-md-2">
                <div class="premium-icon premium-icon-ads d-flex justify-content-center align-items-center">
                    <?= Icon::fa('bullhorn') ?>
                </div>
                <div class="premium-description">
                    <div class="title"><?= Yii::t('youdate', 'No ads') ?></div>
                    <div class="description">
                        <?= Yii::t('youdate', 'You\'ll not see any ads') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-2">
                <div class="premium-icon premium-icon-incognito d-flex justify-content-center align-items-center">
                    <?= Icon::fa('user-secret') ?>
                </div>
                <div class="premium-description">
                    <div class="title"><?= Yii::t('youdate', 'Incognito mode') ?></div>
                    <div class="description">
                        <?= Yii::t('youdate', 'View other users\' profiles invisibly') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-2">
                <div class="premium-icon premium-icon-profile-fields d-flex justify-content-center align-items-center">
                    <?= Icon::fa('list-ul') ?>
                </div>
                <div class="premium-description">
                    <div class="title"><?= Yii::t('youdate', 'Profile info') ?></div>
                    <div class="description">
                        <?= Yii::t('youdate', 'See additional profile fields') ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($likesForPremiumOnly): ?>
            <div class="col-12 col-md-6">
                <div class="premium-about-item d-flex flex-row align-items-center mb-2 mb-sm-2">
                    <div class="premium-icon premium-icon-likes d-flex justify-content-center align-items-center">
                        <?= Icon::fa('heart') ?>
                    </div>
                    <div class="premium-description">
                        <div class="title"><?= Yii::t('youdate', 'Incoming likes') ?></div>
                        <div class="description">
                            <?= Yii::t('youdate', 'See who liked your profile') ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
