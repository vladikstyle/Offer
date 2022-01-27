<?php

use app\plugins\Plugin;
use app\helpers\Html;
use rmrevin\yii\fontawesome\FA;

/** @var $this \yii\web\View */
/** @var $plugin Plugin */
/** @var $pluginId string */
/** @var $enabledPlugins \app\plugins\Plugin[] */
?>
<div class="col-md-4">
    <div class="box box-<?= $plugin->isEnabled ? 'primary' : 'default' ?> box-plugin">
        <div class="box-body no-padding">
            <div class="plugin-content">
                <?= Html::img($plugin->getImage(), ['class' => 'plugin-image']) ?>
                <div class="plugin-info">
                    <h4 class="plugin-title"><?= Html::encode($plugin->getTitle()) ?></h4>
                    <div class="plugin-description">
                        <?= Html::encode($plugin->getDescription()) ?>
                    </div>
                </div>
            </div>
            <div class="plugin-author">
                <strong><?= Html::encode($plugin->getAuthor()) ?></strong>
                <span class="px-1">&middot;</span>
                <?= Html::a(Html::encode($plugin->getWebsite()), $plugin->getWebsite()) ?>
                <div class="plugin-version">
                    <span class="label label-default"><?= Html::encode($plugin->getVersion()) ?></span>
                </div>
            </div>
            <div class="plugin-actions">
                <?php if ($plugin->isEnabled): ?>

                    <!-- Disable button -->
                    <?= Html::a(FA::icon('ban') . 'Disable', ['disable', 'pluginId' => $pluginId], [
                        'class' => 'btn btn-warning btn-slow-action btn-icon-with-text',
                        'data-method' => 'post'
                    ]) ?>

                    <!-- Settings button -->
                    <?php if ($plugin instanceof \app\settings\HasSettings): ?>
                        <?= Html::a(FA::icon('cog') . 'Settings', ['settings', 'pluginId' => $pluginId], [
                            'class' => 'btn btn-primary btn-icon-with-text',
                        ]) ?>
                    <?php endif; ?>

                <?php else: ?>

                    <!-- Enable button -->
                    <?= Html::a(FA::icon('check') . 'Enable', ['enable', 'pluginId' => $pluginId], [
                        'class' => 'btn btn-primary btn-slow-action btn-icon-with-text',
                        'data-method' => 'post'
                    ]) ?>

                <?php endif ?>

                <!-- Uninstall button -->
                <?= Html::a(FA::icon('trash'), ['uninstall', 'pluginId' => $pluginId], [
                    'class' => 'btn btn-danger btn-slow-action pull-right',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('app', 'Are you sure you want to uninstall this plugin?'),
                ]) ?>
            </div>
        </div>
    </div>
</div>
