<?php

use app\helpers\Html;
use yii\bootstrap\Nav;

/** @var $content string */
/** @var $this \app\base\View */

$this->beginContent('@app/modules/admin/views/layouts/main.php');
?>
<div class="box box-solid box-nav">
    <div class="box-body no-padding clearfix">
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills pull-left',
                    ],
                    'items' => [
                        [
                            'label' => Yii::t('app', 'News'),
                            'url' => ['news/index']
                        ],
                    ]
                ]) ?>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="box-actions">
                    <?php if (isset($this->blocks['actionButtons'])): ?>
                        <?= $this->blocks['actionButtons'] ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $content ?>

<?php $this->endContent() ?>
