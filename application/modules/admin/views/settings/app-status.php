<?php

use app\modules\admin\components\AppStatus;
use app\helpers\Html;

/** @var $appChecks array */
/** @var $appStatus array */

$this->params['appStatus'] = $appStatus;
?>

<?php $this->beginContent('@app/modules/admin/views/settings/_layout.php') ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Application status') ?></h3>
    </div>
    <div class="box-body">
        <?php foreach ($appChecks as $appCheck): ?>
            <?php $item = $appCheck() ?>
                <?php if ($item !== null): ?>
                    <div class="app-status-item">
                        <div class="icon <?= $item['status'] ?>">
                            <i class="fa fa-<?= $item['status'] == AppStatus::FAIL ? 'warning' : 'check' ?>"></i>
                        </div>
                        <div>
                            <div class="item"><?= $item['title'] ?></div>
                            <?php if (isset($item['description'])): ?>
                                <div class="description"><?= $item['description'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="box-footer">
        <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('app', 'Reset cache and re-check'),
            ['cached-data', 'type' => 'cache', 'redirect' => 'appStatus'],
            ['class' => 'btn btn-primary', 'data-method' => 'post']
        ) ?>
    </div>
</div>

<?php $this->endContent() ?>
