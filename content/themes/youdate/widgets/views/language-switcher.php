<?php

use app\helpers\Html;
use app\helpers\Url;
use youdate\helpers\Icon;

/** @var $currentLanguage \app\models\Language */
/** @var $availableLanguages \app\models\Language[] */

?>
<a href="#" class="language-switcher-current" data-toggle="modal" data-target="#modal-language-switcher">
    <?= Html::img("@themeUrl/static/images/flags/{$currentLanguage->country}.svg") ?>
    <span><?= Html::encode($currentLanguage->name) ?></span>
</a>

<div class="modal modal-form modal-language-switcher fade" id="modal-language-switcher" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= Yii::t('youdate', 'Current language: {0}', $currentLanguage->name) ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php foreach ($availableLanguages as $language): ?>
                    <a class="language-item d-flex align-items-center" href="<?= Url::to(['site/change-language', 'language' => $language->language_id]) ?>">
                        <?= Html::img("@themeUrl/static/images/flags/{$language->country}.svg", ['class' => 'language-switcher-flag']) ?>
                        <div class="text-left">
                            <div class="language-name"><?= Html::encode($language->name) ?></div>
                            <div class="language-name-ascii"><?= Html::encode($language->name_ascii) ?></div>
                        </div>
                        <?php if ($currentLanguage->language_id == $language->language_id): ?>
                        <div class="language-name-active ml-auto">
                            <span><?= Yii::t('youdate', 'current') ?></span>
                            <?= Icon::fe('check') ?>
                        </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?= Yii::t('youdate', 'Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>
